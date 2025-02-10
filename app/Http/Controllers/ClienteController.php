<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Plano;
use App\Models\User;
use App\Notifications\PortalAccessInviteNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $clientes = Cliente::query()
            ->with('plano')
            ->when($request->filled('busca'), function ($query) use ($request) {
                $busca = $request->string('busca');
                $query->where('nome_empresa', 'like', "%{$busca}%")
                    ->orWhere('cnpj', 'like', "%{$busca}%");
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderBy('nome_empresa')
            ->paginate(15)
            ->withQueryString();

        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        $planos = Plano::where('ativo', true)->orderBy('ordem')->orderBy('nome')->get();

        return view('clientes.create', compact('planos'));
    }

    public function store(Request $request)
    {
        $data = $this->validateCliente($request);

        $criarAcesso = $request->boolean('criar_acesso');

        if ($criarAcesso) {
            $request->validate([
                'email' => ['required', 'email', 'max:191', 'unique:users,email'],
            ]);
            $data['email'] = $request->string('email')->toString();
        }

        $planoPersonalizado = $request->input('plano_tipo') === 'personalizado';
        if ($planoPersonalizado) {
            $data['plano_id'] = null;
        }

        $cliente = Cliente::create($data);

        if ($planoPersonalizado) {
            $plano = $this->resolverPlanoPersonalizado($request, $cliente);
            $cliente->update(['plano_id' => $plano->id]);
        }

        $conviteEnviado = null;
        if ($criarAcesso) {
            $conviteEnviado = $this->criarLoginPortal($cliente, $data, $data['email']);
        }

        return redirect()->route('clientes.index')->with('status', match (true) {
            ! $criarAcesso => 'Cliente cadastrado com sucesso.',
            $conviteEnviado => "Cliente cadastrado com sucesso. Convite de acesso ao portal enviado para {$data['email']}.",
            default => "Cliente cadastrado com sucesso, mas não foi possível enviar o e-mail de convite para {$data['email']} — confira se o endereço está correto e use \"Enviar e-mail para o cliente definir uma nova senha\" na edição do cliente pra tentar de novo.",
        });
    }

    public function edit(Cliente $cliente)
    {
        $planos = Plano::where('ativo', true)
            ->orWhere('id', $cliente->plano_id)
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();

        $portalUser = $cliente->usuarios()->first();

        return view('clientes.edit', compact('cliente', 'planos', 'portalUser'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $this->validateCliente($request, $cliente->id);

        if ($request->input('plano_tipo') === 'personalizado') {
            $plano = $this->resolverPlanoPersonalizado($request, $cliente);
            $data['plano_id'] = $plano->id;
        }

        $portalUser = $cliente->usuarios()->first();
        $status = 'Cliente atualizado com sucesso.';

        if ($portalUser) {
            if ($data['email'] && $data['email'] !== $portalUser->email) {
                $request->validate([
                    'email' => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($portalUser->id)],
                ]);
                $portalUser->update(['email' => $data['email']]);
            }

            if ($request->boolean('reenviar_acesso_portal')) {
                try {
                    $resendStatus = Password::sendResetLink(['email' => $portalUser->email]);
                    $status = $resendStatus === Password::RESET_LINK_SENT
                        ? "Cliente atualizado com sucesso. Link para o cliente definir uma nova senha enviado para {$portalUser->email}."
                        : 'Cliente atualizado com sucesso. '.__($resendStatus);
                } catch (\Throwable $e) {
                    report($e);
                    $status = "Cliente atualizado com sucesso, mas não foi possível enviar o e-mail — confira se o endereço {$portalUser->email} está correto.";
                }
            }
        } elseif ($request->boolean('criar_acesso')) {
            $request->validate([
                'email' => ['required', 'email', 'max:191', 'unique:users,email'],
            ]);
            $data['email'] = $request->string('email')->toString();
            $conviteEnviado = $this->criarLoginPortal($cliente, $data, $data['email']);
            $status = $conviteEnviado
                ? "Cliente atualizado com sucesso. Convite de acesso ao portal enviado para {$data['email']}."
                : "Cliente atualizado com sucesso, mas não foi possível enviar o e-mail de convite para {$data['email']} — confira se o endereço está correto e use \"Enviar e-mail para o cliente definir uma nova senha\" pra tentar de novo.";
        }

        $cliente->update($data);

        return redirect()->route('clientes.index')->with('status', $status);
    }

    public function updateStatus(Request $request, Cliente $cliente)
    {
        $request->validate([
            'status' => 'required|in:ativo,pausado,arquivado',
        ]);

        $cliente->update(['status' => $request->string('status')]);

        return redirect()->route('clientes.index')->with('status', 'Status do cliente atualizado.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();

        return redirect()->route('clientes.index')->with('status', 'Cliente removido.');
    }

    private function criarLoginPortal(Cliente $cliente, array $data, string $email): bool
    {
        $user = User::create([
            'name' => ($data['responsavel'] ?? null) ?: $data['nome_empresa'],
            'email' => $email,

            'password' => Str::random(40),
            'cliente_id' => $cliente->id,
        ]);
        $user->assignRole('cliente');

        try {
            Password::sendResetLink(['email' => $email], function ($user, $token) use ($data) {
                $user->notify(new PortalAccessInviteNotification($token, $data['nome_empresa']));
            });

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    private function validateCliente(Request $request, ?int $clienteId = null): array
    {
        $data = $request->validate([
            'nome_empresa' => 'required|string|max:191',
            'cnpj' => ['nullable', 'string', 'max:20', 'unique:clientes,cnpj,'.$clienteId],
            'responsavel' => 'nullable|string|max:191',
            'telefone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:191',
            'endereco' => 'nullable|string|max:191',
            'site' => 'nullable|string|max:191',
            'observacoes' => 'nullable|string',
            'status' => 'required|in:ativo,pausado,arquivado',
            'mensagens_proativas_habilitado' => 'boolean',
            'leads_portal_habilitado' => 'boolean',
            'plano_id' => 'nullable|exists:planos,id',
            'plano_tipo' => 'nullable|in:catalogo,personalizado',
            'pers_preco_mensal' => 'nullable|required_if:plano_tipo,personalizado|numeric|min:0',
            'pers_limite_conversas_mensais' => 'nullable|required_if:plano_tipo,personalizado|integer|min:1',
            'pers_preco_conversa_excedente' => 'nullable|numeric|min:0',
            'pers_limite_agentes' => 'nullable|integer|min:1',
            'pers_preco_agente_adicional' => 'nullable|numeric|min:0',
            'pers_taxa_implantacao' => 'nullable|numeric|min:0',
            'pers_preco_anexos_adicional' => 'nullable|numeric|min:0',
        ]);

        $data['plano_id'] = ($data['plano_id'] ?? null) ?: null;

        $data = array_diff_key($data, array_flip([
            'plano_tipo', 'pers_preco_mensal', 'pers_limite_conversas_mensais',
            'pers_preco_conversa_excedente', 'pers_limite_agentes', 'pers_preco_agente_adicional',
            'pers_taxa_implantacao', 'pers_preco_anexos_adicional',
        ]));

        return $data;
    }

    private function resolverPlanoPersonalizado(Request $request, Cliente $cliente): Plano
    {
        $limiteAgentes = $request->filled('pers_limite_agentes')
            ? (int) $request->input('pers_limite_agentes')
            : null;

        $dadosPlano = [
            'nome' => 'Personalizado — '.$cliente->nome_empresa,
            'ativo' => false,
            'personalizado' => true,
            'cliente_id' => $cliente->id,
            'preco_mensal' => $request->input('pers_preco_mensal'),
            'taxa_implantacao' => $request->input('pers_taxa_implantacao') ?: null,
            'limite_conversas_mensais' => (int) $request->input('pers_limite_conversas_mensais'),
            'preco_conversa_excedente' => $request->input('pers_preco_conversa_excedente') ?: null,
            'limite_agentes' => $limiteAgentes,
            'preco_agente_adicional' => $limiteAgentes === null ? null : ($request->input('pers_preco_agente_adicional') ?: null),
            'permite_anexos' => $request->boolean('pers_permite_anexos'),
            'preco_anexos_adicional' => $request->boolean('pers_permite_anexos') ? null : ($request->input('pers_preco_anexos_adicional') ?: null),
        ];

        $plano = Plano::where('cliente_id', $cliente->id)->where('personalizado', true)->first();

        if ($plano) {
            $plano->update($dadosPlano);

            return $plano;
        }

        return Plano::create($dadosPlano);
    }
}
