<?php

namespace App\Services;

use App\Models\Campanha;
use App\Models\Cliente;
use App\Models\CustoInfraestrutura;
use App\Models\FechamentoFinanceiro;
use App\Models\Mensagem;
use Illuminate\Support\Carbon;

class FechamentoFinanceiroService
{
    public function __construct(private ExcedentePlanoService $excedenteService) {}

    public function fecharCompetencia(Carbon $competencia, bool $forcar = false, ?int $fechadoPorId = null): array
    {
        $inicioMes = $competencia->copy()->startOfMonth();
        $fimMes = $competencia->copy()->endOfMonth();

        $clientesAtivos = Cliente::where('status', 'ativo')->get();
        $totalClientesAtivos = $clientesAtivos->count();

        $custoInfraTotal = CustoInfraestrutura::totalVigenteEm($inicioMes);
        $custoInfraPorCliente = $totalClientesAtivos > 0 ? $custoInfraTotal / $totalClientesAtivos : 0.0;

        $criados = 0;
        $atualizados = 0;
        $pulados = 0;

        foreach ($clientesAtivos as $cliente) {
            $existente = FechamentoFinanceiro::where('cliente_id', $cliente->id)
                ->whereDate('competencia', $inicioMes->toDateString())
                ->first();

            if ($existente && ! $forcar) {
                $pulados++;

                continue;
            }

            $cliente->load('plano');
            $plano = $cliente->plano;

            $ehPrimeiroFechamento = ! FechamentoFinanceiro::where('cliente_id', $cliente->id)
                ->when($existente, fn ($q) => $q->where('id', '!=', $existente->id))
                ->exists();

            $receitaRecorrente = (float) ($plano->preco_mensal ?? 0);
            $receitaImplantacao = ($plano && $ehPrimeiroFechamento) ? (float) ($plano->taxa_implantacao ?? 0) : 0.0;

            $excedente = $this->excedenteService->calcular($cliente, $competencia);
            $receitaExcedente = $excedente['total'];

            $receitaCampanhas = (float) Campanha::where('cliente_id', $cliente->id)
                ->where('status', 'concluida')
                ->whereBetween('enviado_em', [$inicioMes, $fimMes])
                ->sum('valor_cobrado');

            $custoIa = (float) $cliente->conversas()
                ->whereBetween('iniciada_em', [$inicioMes, $fimMes])
                ->sum('custo_estimado');

            $custoMeta = (float) Mensagem::whereHas('conversa', function ($q) use ($cliente, $inicioMes, $fimMes) {
                $q->where('cliente_id', $cliente->id)->whereBetween('iniciada_em', [$inicioMes, $fimMes]);
            })->sum('custo_meta');

            $receitaTotal = $receitaRecorrente + $receitaImplantacao + $receitaExcedente + $receitaCampanhas;
            $custoTotal = $custoIa + $custoMeta + $custoInfraPorCliente;
            $lucroBruto = $receitaTotal - $custoTotal;
            $margem = $receitaTotal > 0 ? round(($lucroBruto / $receitaTotal) * 100, 2) : null;

            $dados = [
                'receita_recorrente' => round($receitaRecorrente, 2),
                'receita_implantacao' => round($receitaImplantacao, 2),
                'receita_excedente' => round($receitaExcedente, 2),
                'receita_campanhas' => round($receitaCampanhas, 2),
                'conversas_no_mes' => $excedente['conversas_no_mes'],
                'limite_conversas_plano' => $excedente['limite_conversas'],
                'conversas_excedentes' => $excedente['conversas_excedentes'],
                'valor_conversas_excedentes' => $excedente['valor_conversas_excedentes'],
                'agentes_no_mes' => $excedente['agentes_no_mes'],
                'limite_agentes_plano' => $excedente['limite_agentes'],
                'agentes_extras' => $excedente['agentes_extras'],
                'valor_agentes_extras' => $excedente['valor_agentes_extras'],
                'anexos_cobrados' => $excedente['anexos_cobrados'],
                'valor_anexos' => $excedente['valor_anexos'],
                'custo_ia' => round($custoIa, 4),
                'custo_meta' => round($custoMeta, 4),
                'custo_infra_rateado' => round($custoInfraPorCliente, 2),
                'lucro_bruto' => round($lucroBruto, 2),
                'margem_percentual' => $margem,
                'fechado_por_id' => $fechadoPorId,
                'fechado_em' => now(),
            ];

            if ($existente) {
                $existente->update($dados);
                $atualizados++;
            } else {
                FechamentoFinanceiro::create(array_merge($dados, [
                    'cliente_id' => $cliente->id,
                    'competencia' => $inicioMes->toDateString(),
                ]));
                $criados++;
            }
        }

        return [
            'criados' => $criados,
            'atualizados' => $atualizados,
            'pulados' => $pulados,
            'total_clientes' => $totalClientesAtivos,
        ];
    }
}
