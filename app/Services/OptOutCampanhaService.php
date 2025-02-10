<?php

namespace App\Services;

use App\Models\Lead;
use Illuminate\Support\Str;

class OptOutCampanhaService
{
    private const PALAVRAS = ['parar', 'pare', 'sair', 'cancelar', 'descadastrar', 'stop'];

    private const FRASES = [
        'nao quero', 'nao quero mais', 'nao me mande', 'nao me manda',
        'nao envie mais', 'nao envia mais', 'remover da lista', 'sair da lista',
        'cancelar inscricao', 'para de mandar', 'pare de mandar', 'pare de enviar',
    ];

    public function detectarEProcessar(int $clienteId, string $telefone, string $texto): void
    {
        if (! $this->contemGatilho($texto)) {
            return;
        }

        $lead = $this->buscarLeadTolerante($clienteId, $telefone);

        if (! $lead || ! $lead->aceita_campanhas) {
            return;
        }

        $lead->update([
            'aceita_campanhas' => false,
            'opt_out_em' => now(),
        ]);
    }

    private function contemGatilho(string $texto): bool
    {
        $normalizado = Str::lower(Str::ascii($texto));

        foreach (self::FRASES as $frase) {
            if (str_contains($normalizado, $frase)) {
                return true;
            }
        }

        foreach (self::PALAVRAS as $palavra) {
            if (preg_match('/\b'.preg_quote($palavra, '/').'\b/', $normalizado)) {
                return true;
            }
        }

        return false;
    }

    private function buscarLeadTolerante(int $clienteId, string $telefone): ?Lead
    {
        $digitos = preg_replace('/\D/', '', $telefone);

        if (! $digitos || strlen($digitos) < 8) {
            return null;
        }

        $sufixo = substr($digitos, -11);

        return Lead::where('cliente_id', $clienteId)
            ->whereRaw("RIGHT(REGEXP_REPLACE(telefone, '[^0-9]', ''), 11) = ?", [$sufixo])
            ->first();
    }
}
