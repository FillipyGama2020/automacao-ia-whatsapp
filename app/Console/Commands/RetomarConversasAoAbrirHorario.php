<?php

namespace App\Console\Commands;

use App\Jobs\GerarRespostaIaJob;
use App\Models\Conversa;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:retomar-conversas-ao-abrir-horario')]
#[Description('Retoma sozinha, sem esperar o contato escrever de novo, uma conversa que ficou parada só no aviso de "fora de horário" — assim que o horário de atendimento do agente reabrir (dentro da janela de 24h de mensagem livre da Meta). Só age em agentes com retomar_ao_abrir_horario habilitado.')]
class RetomarConversasAoAbrirHorario extends Command
{
    public function handle(): int
    {
        $janelaHoras = config('conversas.janela_inatividade_horas');

        $conversas = Conversa::whereIn('status', ['em_andamento', 'transferida_humano'])
            ->where('ultima_mensagem_em', '>=', now()->subHours($janelaHoras))
            ->whereHas('agente', fn ($q) => $q->where('retomar_ao_abrir_horario', true))
            ->with('agente.horarios', 'agente.feriados')
            ->get();

        $retomadas = 0;

        foreach ($conversas as $conversa) {
            $ultimaMensagem = $conversa->mensagens()->reorder()->latest('id')->first();

            if (! $ultimaMensagem || $ultimaMensagem->remetente !== 'agente_ia' || ! $ultimaMensagem->fora_horario) {
                continue;
            }

            if (! $conversa->agente || ! $conversa->agente->estaAberto()) {
                continue;
            }

            $ultimaMensagemContato = $conversa->mensagens()
                ->reorder()
                ->where('remetente', 'contato')
                ->latest('id')
                ->first();

            if (! $ultimaMensagemContato) {
                continue;
            }

            GerarRespostaIaJob::dispatch($conversa->id, $ultimaMensagemContato->id);
            $retomadas++;
        }

        $this->info("{$retomadas} conversa(s) retomada(s) com a reabertura do horário.");

        return self::SUCCESS;
    }
}
