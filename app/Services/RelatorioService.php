<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Conversa;
use App\Models\FechamentoFinanceiro;
use App\Models\Lead;
use App\Models\Mensagem;
use Illuminate\Support\Carbon;

class RelatorioService
{
    public function gerarParaCliente(Cliente $cliente, Carbon $competencia): array
    {
        $inicioMes = $competencia->copy()->startOfMonth();
        $fimMes = $competencia->copy()->endOfMonth();

        $conversas = $cliente->conversas()->whereBetween('iniciada_em', [$inicioMes, $fimMes]);

        return [
            'cliente' => $cliente,
            'competencia' => $competencia,
            'atendimento' => $this->atendimento(clone $conversas),
            'mensagens' => $this->mensagens($cliente->id, $inicioMes, $fimMes),
            'leads' => $this->leads($cliente->id, $inicioMes, $fimMes),
            'horarios' => $this->distribuicaoPorHora($cliente->id, $inicioMes, $fimMes),
            'custo_ia' => (float) (clone $conversas)->sum('custo_estimado'),
            'financeiro' => FechamentoFinanceiro::where('cliente_id', $cliente->id)
                ->whereDate('competencia', $inicioMes->toDateString())
                ->first(),
        ];
    }

    public function gerarConsolidado(Carbon $competencia): array
    {
        $inicioMes = $competencia->copy()->startOfMonth();
        $fimMes = $competencia->copy()->endOfMonth();

        $conversas = Conversa::whereBetween('iniciada_em', [$inicioMes, $fimMes]);

        $fechamentos = FechamentoFinanceiro::with('cliente')
            ->whereDate('competencia', $inicioMes->toDateString())
            ->get();

        return [
            'competencia' => $competencia,
            'atendimento' => $this->atendimento(clone $conversas),
            'mensagens' => $this->mensagens(null, $inicioMes, $fimMes),
            'leads' => $this->leads(null, $inicioMes, $fimMes),
            'horarios' => $this->distribuicaoPorHora(null, $inicioMes, $fimMes),
            'custo_ia' => (float) (clone $conversas)->sum('custo_estimado'),
            'financeiro' => $this->financeiroConsolidado($fechamentos),
            'por_cliente' => $this->resumoPorCliente($inicioMes, $fimMes, $fechamentos),
        ];
    }

    private function atendimento($conversasQuery): array
    {
        $total = (clone $conversasQuery)->count();

        $porStatus = [];
        foreach (Conversa::statusLabels() as $valor => $label) {
            $porStatus[$label] = (clone $conversasQuery)->where('status', $valor)->count();
        }

        $avaliacaoMedia = (clone $conversasQuery)->whereNotNull('avaliacao')->avg('avaliacao');

        return [
            'total' => $total,
            'por_status' => $porStatus,
            'avaliacao_media' => $avaliacaoMedia !== null ? round((float) $avaliacaoMedia, 2) : null,
        ];
    }

    private function mensagens(?int $clienteId, Carbon $inicioMes, Carbon $fimMes): array
    {
        $query = Mensagem::whereHas('conversa', function ($q) use ($clienteId, $inicioMes, $fimMes) {
            $q->whereBetween('enviada_em', [$inicioMes, $fimMes]);

            if ($clienteId) {
                $q->where('cliente_id', $clienteId);
            }
        });

        $total = (clone $query)->count();

        $porTipoBruto = (clone $query)
            ->selectRaw('tipo, COUNT(*) as total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo');

        $porRemetenteBruto = (clone $query)
            ->selectRaw('remetente, COUNT(*) as total')
            ->groupBy('remetente')
            ->pluck('total', 'remetente');

        $porTipo = [];
        foreach (Mensagem::tipoLabels() as $valor => $label) {
            $porTipo[$label] = $porTipoBruto[$valor] ?? 0;
        }

        $porRemetente = [];
        foreach (Mensagem::remetenteLabels() as $valor => $label) {
            $porRemetente[$label] = $porRemetenteBruto[$valor] ?? 0;
        }

        return [
            'total' => $total,
            'por_tipo' => $porTipo,
            'por_remetente' => $porRemetente,
        ];
    }

    private function leads(?int $clienteId, Carbon $inicioMes, Carbon $fimMes): array
    {
        $query = Lead::whereBetween('capturado_em', [$inicioMes, $fimMes])
            ->when($clienteId, fn ($q) => $q->where('cliente_id', $clienteId));

        $porClassificacao = [];
        foreach (Lead::classificacaoLabels() as $valor => $label) {
            $porClassificacao[$label] = (clone $query)->where('classificacao', $valor)->count();
        }

        $porStatus = [];
        foreach (Lead::statusLabels() as $valor => $label) {
            $porStatus[$label] = (clone $query)->where('status', $valor)->count();
        }

        return [
            'total' => (clone $query)->count(),
            'por_classificacao' => $porClassificacao,
            'por_status' => $porStatus,
        ];
    }

    private function distribuicaoPorHora(?int $clienteId, Carbon $inicioMes, Carbon $fimMes): array
    {
        $porHora = Mensagem::whereHas('conversa', function ($q) use ($clienteId, $inicioMes, $fimMes) {
            $q->whereBetween('enviada_em', [$inicioMes, $fimMes]);

            if ($clienteId) {
                $q->where('cliente_id', $clienteId);
            }
        })
            ->selectRaw('HOUR(enviada_em) as hora, COUNT(*) as total')
            ->groupBy('hora')
            ->pluck('total', 'hora')
            ->all();

        $distribuicao = [];
        for ($hora = 0; $hora < 24; $hora++) {
            $distribuicao[$hora] = $porHora[$hora] ?? 0;
        }

        return $distribuicao;
    }

    private function financeiroConsolidado($fechamentos): ?array
    {
        if ($fechamentos->isEmpty()) {
            return null;
        }

        $receitaTotal = $fechamentos->sum(fn ($f) => $f->receitaTotal());
        $custoTotal = $fechamentos->sum(fn ($f) => $f->custoTotal());
        $lucroBruto = $fechamentos->sum('lucro_bruto');

        return [
            'receita_total' => $receitaTotal,
            'custo_total' => $custoTotal,
            'lucro_bruto' => $lucroBruto,
            'margem_percentual' => $receitaTotal > 0 ? round(($lucroBruto / $receitaTotal) * 100, 2) : null,
            'receita_excedente' => $fechamentos->sum('receita_excedente'),
        ];
    }

    private function resumoPorCliente(Carbon $inicioMes, Carbon $fimMes, $fechamentos): array
    {
        $fechamentosPorCliente = $fechamentos->keyBy('cliente_id');

        return Cliente::orderBy('nome_empresa')->get()->map(function (Cliente $cliente) use ($inicioMes, $fimMes, $fechamentosPorCliente) {
            $conversas = $cliente->conversas()->whereBetween('iniciada_em', [$inicioMes, $fimMes]);

            return [
                'cliente' => $cliente,
                'conversas' => (clone $conversas)->count(),
                'leads' => $cliente->leads()->whereBetween('capturado_em', [$inicioMes, $fimMes])->count(),
                'custo_ia' => (float) (clone $conversas)->sum('custo_estimado'),
                'fechamento' => $fechamentosPorCliente->get($cliente->id),
            ];
        })->all();
    }
}
