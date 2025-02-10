<?php

namespace App\Services;

use App\Models\Cliente;
use Illuminate\Support\Carbon;

class ExcedentePlanoService
{
    public function calcular(Cliente $cliente, Carbon $competencia): array
    {
        $cliente->load('plano');
        $plano = $cliente->plano;

        if (! $plano) {
            return $this->resultadoVazio();
        }

        $inicioMes = $competencia->copy()->startOfMonth();
        $fimMes = $competencia->copy()->endOfMonth();

        $totalConversas = $cliente->conversas()
            ->whereBetween('iniciada_em', [$inicioMes, $fimMes])
            ->where('custo_estimado', '>', 0)
            ->count();

        $conversasExcedentes = 0;
        $valorConversasExcedentes = 0.0;

        if (! $plano->conversasIlimitadas()) {
            $conversasExcedentes = max(0, $totalConversas - $plano->limite_conversas_mensais);
            $valorConversasExcedentes = $conversasExcedentes * (float) ($plano->preco_conversa_excedente ?? 0);
        }

        $agentesDoMes = $cliente->agentes()
            ->withTrashed()
            ->where('created_at', '<=', $fimMes)
            ->where(function ($q) use ($inicioMes) {
                $q->whereNull('deleted_at')->orWhere('deleted_at', '>=', $inicioMes);
            });

        $totalAgentes = (clone $agentesDoMes)->count();
        $agentesExtras = 0;
        $valorAgentesExtras = 0.0;

        if (! $plano->agentesIlimitados()) {
            $agentesExtras = max(0, $totalAgentes - $plano->limite_agentes);
            $valorAgentesExtras = $agentesExtras * (float) ($plano->preco_agente_adicional ?? 0);
        }

        $anexosCobrados = false;
        $valorAnexos = 0.0;

        if (! $plano->permite_anexos) {
            $anexosCobrados = (clone $agentesDoMes)->where('permitir_anexos', true)->exists();
            $valorAnexos = $anexosCobrados ? (float) ($plano->preco_anexos_adicional ?? 0) : 0.0;
        }

        return [
            'conversas_no_mes' => $totalConversas,
            'limite_conversas' => $plano->conversasIlimitadas() ? null : $plano->limite_conversas_mensais,
            'conversas_excedentes' => $conversasExcedentes,
            'valor_conversas_excedentes' => round($valorConversasExcedentes, 2),
            'agentes_no_mes' => $totalAgentes,
            'limite_agentes' => $plano->agentesIlimitados() ? null : $plano->limite_agentes,
            'agentes_extras' => $agentesExtras,
            'valor_agentes_extras' => round($valorAgentesExtras, 2),
            'anexos_cobrados' => $anexosCobrados,
            'valor_anexos' => round($valorAnexos, 2),
            'total' => round($valorConversasExcedentes + $valorAgentesExtras + $valorAnexos, 2),
        ];
    }

    private function resultadoVazio(): array
    {
        return [
            'conversas_no_mes' => 0,
            'limite_conversas' => null,
            'conversas_excedentes' => 0,
            'valor_conversas_excedentes' => 0.0,
            'agentes_no_mes' => 0,
            'limite_agentes' => null,
            'agentes_extras' => 0,
            'valor_agentes_extras' => 0.0,
            'anexos_cobrados' => false,
            'valor_anexos' => 0.0,
            'total' => 0.0,
        ];
    }
}
