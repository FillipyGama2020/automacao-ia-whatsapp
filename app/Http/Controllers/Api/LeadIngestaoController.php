<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversa;
use App\Models\Lead;
use App\Models\WhatsappIntegracao;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadIngestaoController extends Controller
{
    public function store(Request $request)
    {
        $integracao = $request->attributes->get('integracao');

        $data = $request->validate([
            'telefone' => 'required|string|max:30',
            'nome' => 'nullable|string|max:191',
            'email' => 'nullable|email|max:191',
            'interesse' => 'nullable|string',
            'classificacao' => 'nullable|in:frio,morno,quente',
            'conversa_id' => [
                'nullable',
                'integer',
                Rule::exists('conversas', 'id')->where('cliente_id', $integracao->cliente_id),
            ],
            'agente_id' => [
                'nullable',
                'integer',
                Rule::exists('agentes', 'id')->where('cliente_id', $integracao->cliente_id),
            ],
        ]);

        $agenteId = $data['agente_id'] ?? null;

        if (! empty($data['conversa_id']) && ! $agenteId) {
            $agenteId = Conversa::find($data['conversa_id'])->agente_id;
        }

        $valores = array_intersect_key($data, array_flip(['nome', 'email', 'interesse', 'classificacao']));

        if (! empty($data['conversa_id'])) {
            $valores['conversa_id'] = $data['conversa_id'];
        }

        if ($agenteId) {
            $valores['agente_id'] = $agenteId;
        }

        $lead = Lead::where('cliente_id', $integracao->cliente_id)
            ->where('telefone', $data['telefone'])
            ->first();

        if ($lead) {
            $lead->update($valores);

            return response()->json(['criado' => false, 'lead_id' => $lead->id], 200);
        }

        $lead = Lead::create(array_merge($valores, [
            'cliente_id' => $integracao->cliente_id,
            'telefone' => $data['telefone'],
            'origem' => 'whatsapp_ia',
            'status' => 'novo',
            'capturado_em' => now(),
        ]));

        return response()->json(['criado' => true, 'lead_id' => $lead->id], 201);
    }
}
