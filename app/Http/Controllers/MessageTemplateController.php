<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\MessageTemplate;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class MessageTemplateController extends Controller
{
    public function index(Request $request)
    {
        $templates = MessageTemplate::with('cliente')
            ->when($request->filled('cliente_id'), fn ($q) => $q->where('cliente_id', $request->cliente_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $clientes = Cliente::orderBy('nome_empresa')->get(['id', 'nome_empresa']);

        return view('message-templates.index', compact('templates', 'clientes'));
    }

    public function create()
    {
        $template = new MessageTemplate();
        $clientes = Cliente::orderBy('nome_empresa')->get(['id', 'nome_empresa']);

        return view('message-templates.create', compact('template', 'clientes'));
    }

    public function store(Request $request)
    {
        $data = $this->validateTemplate($request);
        $data['status'] = 'rascunho';

        MessageTemplate::create($data);

        return redirect()->route('message-templates.index')->with('status', 'Template criado como rascunho.');
    }

    public function edit(MessageTemplate $messageTemplate)
    {
        abort_if($messageTemplate->status !== 'rascunho', 404, 'Um template já submetido não pode mais ser editado.');

        $clientes = Cliente::orderBy('nome_empresa')->get(['id', 'nome_empresa']);

        return view('message-templates.edit', ['template' => $messageTemplate, 'clientes' => $clientes]);
    }

    public function update(Request $request, MessageTemplate $messageTemplate)
    {
        abort_if($messageTemplate->status !== 'rascunho', 404, 'Um template já submetido não pode mais ser editado.');

        $data = $this->validateTemplate($request, $messageTemplate->id);

        $messageTemplate->update($data);

        return redirect()->route('message-templates.index')->with('status', 'Template atualizado.');
    }

    public function destroy(MessageTemplate $messageTemplate)
    {
        if ($messageTemplate->status !== 'rascunho') {
            return redirect()->route('message-templates.index')
                ->with('error', 'Só é possível remover um template que ainda está em rascunho (nunca submetido pra Meta).');
        }

        $messageTemplate->delete();

        return redirect()->route('message-templates.index')->with('status', 'Template removido.');
    }

    public function submeter(MessageTemplate $messageTemplate)
    {
        if ($messageTemplate->status !== 'rascunho') {
            return redirect()->route('message-templates.index')
                ->with('error', 'Este template já foi submetido antes.');
        }

        $integracao = $messageTemplate->cliente->whatsappIntegracao;

        if (! $integracao || ! $integracao->business_account_id || ! $integracao->access_token) {
            return redirect()->route('message-templates.index')
                ->with('error', 'Este cliente não tem uma conta do WhatsApp conectada (WABA) — conecte antes de submeter um template.');
        }

        $version = config('services.meta.graph_version');

        try {
            $response = Http::withToken($integracao->access_token)
                ->timeout(15)
                ->post("https://graph.facebook.com/{$version}/{$integracao->business_account_id}/message_templates", [
                    'name' => $messageTemplate->nome,
                    'language' => $messageTemplate->idioma,
                    'category' => strtoupper($messageTemplate->categoria),
                    'components' => [
                        ['type' => 'BODY', 'text' => $messageTemplate->corpo],
                    ],
                ]);
        } catch (ConnectionException $e) {
            report($e);

            return redirect()->route('message-templates.index')
                ->with('error', 'Não foi possível contatar a Meta. Tente novamente em instantes.');
        }

        if (! $response->successful()) {
            return redirect()->route('message-templates.index')
                ->with('error', 'A Meta recusou o template: '.($response->json('error.message') ?? 'erro desconhecido.'));
        }

        $messageTemplate->update([
            'status' => 'pendente',
            'meta_template_id' => $response->json('id'),
            'enviado_em' => now(),
        ]);

        return redirect()->route('message-templates.index')
            ->with('status', 'Template submetido — a aprovação da Meta pode levar de minutos a até 24h.');
    }

    private function validateTemplate(Request $request, ?int $templateId = null): array
    {
        $data = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'nome' => [
                'required', 'string', 'max:191', 'regex:/^[a-z0-9_]+$/',
                Rule::unique('message_templates')
                    ->where(fn ($q) => $q->where('cliente_id', $request->cliente_id)->where('idioma', $request->idioma))
                    ->ignore($templateId),
            ],
            'idioma' => 'required|string|max:10',
            'categoria' => ['required', Rule::in(array_keys(MessageTemplate::categoriaLabels()))],
            'corpo' => 'required|string|max:1024',
            'variaveis' => 'nullable|array',
            'variaveis.*' => 'nullable|string|max:191',
        ], [
            'nome.regex' => 'O nome só pode ter letras minúsculas, números e underscore (é o identificador usado na API da Meta).',
        ]);

        $data['variaveis'] = array_values(array_filter($data['variaveis'] ?? []));

        return $data;
    }
}
