<?php

namespace App\Console\Commands;

use App\Models\Conversa;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:fechar-conversas-abandonadas')]
#[Description('Marca como "abandonada" as conversas em andamento sem nenhuma mensagem nova dentro da janela de inatividade')]
class FecharConversasAbandonadas extends Command
{
    public function handle(): int
    {
        $horas = config('conversas.janela_inatividade_horas');
        $limite = now()->subHours($horas);

        $conversas = Conversa::where('status', 'em_andamento')
            ->where('ultima_mensagem_em', '<', $limite)
            ->get();

        if ($conversas->isEmpty()) {
            $this->info("Nenhuma conversa parada há mais de {$horas}h para marcar como abandonada.");

            return self::SUCCESS;
        }

        foreach ($conversas as $conversa) {
            $conversa->update([
                'status' => 'abandonada',
                'finalizada_em' => $conversa->ultima_mensagem_em,
            ]);
        }

        $this->info("{$conversas->count()} conversa(s) marcada(s) como abandonada.");

        return self::SUCCESS;
    }
}
