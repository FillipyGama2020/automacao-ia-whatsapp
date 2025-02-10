<?php

namespace App\Console\Commands;

use App\Services\FechamentoFinanceiroService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

#[Signature('app:fechar-mes-financeiro {--competencia= : Mês a fechar, formato AAAA-MM (padrão: mês anterior)} {--forcar : Recalcula e sobrescreve um mês já fechado}')]
#[Description('Fecha o mês financeiro (receita, custo, margem) de cada cliente ativo — congela um snapshot, não recalcula sozinho depois')]
class FecharMesFinanceiro extends Command
{
    public function handle(FechamentoFinanceiroService $service): int
    {
        $competencia = $this->option('competencia')
            ? Carbon::createFromFormat('Y-m', $this->option('competencia'))->startOfMonth()
            : now()->subMonthNoOverflow()->startOfMonth();

        $resultado = $service->fecharCompetencia($competencia, (bool) $this->option('forcar'));

        $this->info(sprintf(
            'Fechamento de %s: %d criado(s), %d atualizado(s), %d pulado(s) (já fechado — use --forcar pra sobrescrever), de %d cliente(s) ativo(s).',
            $competencia->format('m/Y'),
            $resultado['criados'],
            $resultado['atualizados'],
            $resultado['pulados'],
            $resultado['total_clientes']
        ));

        return self::SUCCESS;
    }
}
