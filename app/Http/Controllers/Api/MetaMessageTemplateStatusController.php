<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MetaMessageTemplateStatusController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $payload = $request->all();

        $valor = $payload['entry'][0]['changes'][0]['value'] ?? null;
        $metaTemplateId = $valor['message_template_id'] ?? null;
        $evento = $valor['event'] ?? null;
        $motivo = $valor['reason'] ?? null;

        if (! $metaTemplateId || ! $evento) {
            Log::error('message_template_status_update com payload incompleto', ['payload' => $payload]);

            return response()->json(['ok' => false]);
        }

        $template = MessageTemplate::where('meta_template_id', $metaTemplateId)->first();

        if (! $template) {
            Log::error('message_template_status_update pra meta_template_id desconhecido', ['payload' => $payload]);

            return response()->json(['ok' => false]);
        }

        $mapaStatus = [
            'APPROVED' => 'aprovado',
            'REJECTED' => 'rejeitado',
            'PAUSED' => 'pausado',
            'DISABLED' => 'pausado',
            'PENDING' => 'pendente',
        ];

        $novoStatus = $mapaStatus[$evento] ?? null;

        if ($novoStatus) {
            $dados = ['status' => $novoStatus];

            if ($novoStatus === 'aprovado') {
                $dados['aprovado_em'] = now();
            }

            if ($novoStatus === 'rejeitado' && $motivo && $motivo !== 'NONE') {
                $dados['motivo_rejeicao'] = $motivo;
            }

            $template->update($dados);
        } else {
            Log::error('message_template_status_update com evento não mapeado', ['evento' => $evento, 'template_id' => $template->id]);
        }

        return response()->json(['ok' => true]);
    }
}
