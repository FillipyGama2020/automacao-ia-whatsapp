<?php

namespace App\Console\Commands;

use App\Models\Campanha;
use App\Services\CampanhaService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:processar-campanhas-agendadas')]
#[Description('Dispara as campanhas de mensagens proativas cuja data agendada já chegou')]
class ProcessarCampanhasAgendadas extends Command
{
    public function handle(CampanhaService $campanhaService): int
    {
        $campanhas = Campanha::where('status', 'agendada')
            ->where('agendado_para', '<=', now())
            ->get();

        if ($campanhas->isEmpty()) {
            $this->info('Nenhuma campanha agendada pendente de disparo.');

            return self::SUCCESS;
        }

        foreach ($campanhas as $campanha) {
            $campanhaService->enviarAgora($campanha);
            $this->info("Campanha #{$campanha->id} disparada ({$campanha->total_leads} destinatário(s)).");
        }

        return self::SUCCESS;
    }
}
