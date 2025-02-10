<?php

namespace App\Console\Commands;

use App\Jobs\GerarRespostaIaJob;
use App\Models\Conversa;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:retomar-conversas-sem-resposta')]
#[Description('Reagenda uma resposta da IA para conversas em que o contato ficou sem retorno (falha de sistema/servidor/webhook, ou uma pausa que já deixou de se aplicar) — evita perder lead por uma falha técnica silenciosa')]
class RetomarConversasSemResposta extends Command
{
    public function handle(): int
    {
        $graceMinutos = config('conversas.retomada_falha_sistema_minutos');
        $janelaHoras = config('conversas.janela_inatividade_horas');

        $conversas = Conversa::whereIn('status', ['em_andamento', 'transferida_humano'])
            ->where('ultima_mensagem_em', '>=', now()->subHours($janelaHoras))
            ->where('ultima_mensagem_em', '<=', now()->subMinutes($graceMinutos))
            ->with('agente')
            ->get();

        $retomadas = 0;

        foreach ($conversas as $conversa) {
            $ultimaMensagem = $conversa->mensagens()->reorder()->latest('id')->first();

            if (! $ultimaMensagem || $ultimaMensagem->remetente !== 'contato') {
                continue;
            }

            if ($conversa->status === 'transferida_humano') {
                $agente = $conversa->agente;

                if (! $agente || ! $agente->retomada_automatica_minutos) {
                    continue;
                }

                $ultimaMensagemHumano = $conversa->mensagens()
                    ->reorder()
                    ->where('remetente', 'humano')
                    ->latest('id')
                    ->first();

                if ($ultimaMensagemHumano && $ultimaMensagemHumano->enviada_em->diffInMinutes(now()) < $agente->retomada_automatica_minutos) {
                    continue;
                }
            }

            GerarRespostaIaJob::dispatch($conversa->id, $ultimaMensagem->id);
            $retomadas++;
        }

        $this->info("{$retomadas} conversa(s) reagendada(s) para retomada.");

        return self::SUCCESS;
    }
}
