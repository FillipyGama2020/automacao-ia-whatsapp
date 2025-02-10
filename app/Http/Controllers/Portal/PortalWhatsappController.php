<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\WhatsappIntegracao;
use App\Services\EmbeddedSignupService;
use Illuminate\Http\Request;

class PortalWhatsappController extends Controller
{
    public function __construct(private EmbeddedSignupService $embeddedSignup)
    {
    }

    public function index(Request $request)
    {
        $cliente = $request->user()->cliente;
        $integracoes = $cliente->whatsappIntegracoes()->orderBy('id')->get();

        return view('portal.whatsapp', compact('integracoes'));
    }

    public function conectarEmbedded(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string',
            'waba_id' => 'required|string',
            'phone_number_id' => 'required|string',
        ]);

        $resultado = $this->embeddedSignup->conectar(
            $request->user()->cliente,
            $data['code'],
            $data['waba_id'],
            $data['phone_number_id']
        );

        return response()->json(['message' => $resultado['message']], $resultado['ok'] ? 200 : 422);
    }

    public function iniciarConexao(Request $request)
    {
        $data = $request->validate(['code' => 'required|string']);

        $resultado = $this->embeddedSignup->iniciarConexaoPendente($request->user()->cliente, $data['code']);

        return response()->json(['message' => $resultado['message']], $resultado['ok'] ? 200 : 422);
    }

    public function desconectar(Request $request, WhatsappIntegracao $integracao)
    {
        abort_unless($integracao->cliente_id === $request->user()->cliente_id, 404);

        $ok = $this->embeddedSignup->desconectar($integracao);

        return $ok
            ? redirect()->route('portal.whatsapp')->with('status', 'WhatsApp desconectado do sistema.')
            : redirect()->route('portal.whatsapp')->with('error', 'Nenhuma conexão ativa pra desconectar.');
    }
}
