<?php

namespace App\Jobs;

use App\Models\Mensagem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class GerarRespostaIaJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public $uniqueFor = 60;

    public function __construct(
        public int $conversaId,
        public int $mensagemId,
    ) {}

    public function uniqueId(): string
    {
        return "{$this->conversaId}-{$this->mensagemId}";
    }

    public function handle(): void
    {
        $idMaisRecente = Mensagem::where('conversa_id', $this->conversaId)
            ->where('remetente', 'contato')
            ->max('id');

        if ($idMaisRecente !== $this->mensagemId) {
            return;
        }

        $baseUrl = rtrim(config('services.n8n.webhook_base_url'), '/');

        try {
            Http::timeout(5)->post("{$baseUrl}/webhook/gerar-resposta-ia", [
                'conversa_id' => $this->conversaId,
                'mensagem_id' => $this->mensagemId,
            ]);
        } catch (ConnectionException $e) {
            report($e);
        }
    }
}
