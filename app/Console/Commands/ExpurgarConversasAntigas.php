<?php

namespace App\Console\Commands;

use App\Models\Conversa;
use App\Services\ExclusaoLgpdService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:expurgar-conversas-antigas {--meses=12 : Retenção em meses antes do expurgo automático}')]
#[Description('Apaga conversas (e mídia associada) mais antigas que a retenção definida, conforme política de LGPD')]
class ExpurgarConversasAntigas extends Command
{
    public function handle(ExclusaoLgpdService $service): int
    {
        $meses = (int) $this->option('meses');
        $limite = now()->subMonths($meses);

        $conversas = Conversa::where('iniciada_em', '<', $limite)->get();

        if ($conversas->isEmpty()) {
            $this->info('Nenhuma conversa passou da retenção de '.$meses.' meses.');

            return self::SUCCESS;
        }

        $log = $service->excluir($conversas, 'retencao_automatica');

        $this->info("Expurgo concluído: {$log->quantidade_conversas} conversas e {$log->quantidade_mensagens} mensagens apagadas (retenção: {$meses} meses).");

        return self::SUCCESS;
    }
}
