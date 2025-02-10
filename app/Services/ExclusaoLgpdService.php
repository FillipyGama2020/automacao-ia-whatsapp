<?php

namespace App\Services;

use App\Models\Conversa;
use App\Models\ExclusaoLgpd;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class ExclusaoLgpdService
{
    public function excluir(Collection $conversas, string $motivo, ?string $contatoTelefone = null, ?int $executadoPorId = null): ExclusaoLgpd
    {
        $totalConversas = 0;
        $totalMensagens = 0;

        foreach ($conversas as $conversa) {
            $conversa->loadMissing('mensagens');

            foreach ($conversa->mensagens as $mensagem) {
                if ($mensagem->midia_path) {
                    Storage::disk('public')->delete($mensagem->midia_path);
                }
            }

            $totalMensagens += $conversa->mensagens->count();
            $totalConversas++;

            $conversa->delete();
        }

        $totalLeads = 0;

        if ($motivo === 'solicitacao_titular' && $contatoTelefone) {
            $leads = $this->buscarLeadsPorTelefone($contatoTelefone);
            $totalLeads = $leads->count();

            foreach ($leads as $lead) {
                $lead->delete();
            }
        }

        return ExclusaoLgpd::create([
            'contato_telefone' => $contatoTelefone,
            'motivo' => $motivo,
            'quantidade_conversas' => $totalConversas,
            'quantidade_mensagens' => $totalMensagens,
            'quantidade_leads' => $totalLeads,
            'executado_por_id' => $executadoPorId,
            'executado_em' => now(),
        ]);
    }

    public function buscarPorTelefone(string $telefone): Collection
    {
        $sufixo = $this->sufixoTelefone($telefone);

        if (! $sufixo) {
            return collect();
        }

        return Conversa::with(['cliente', 'agente'])
            ->whereRaw("RIGHT(REGEXP_REPLACE(contato_telefone, '[^0-9]', ''), 11) = ?", [$sufixo])
            ->get();
    }

    public function buscarLeadsPorTelefone(string $telefone): Collection
    {
        $sufixo = $this->sufixoTelefone($telefone);

        if (! $sufixo) {
            return collect();
        }

        return Lead::with('cliente')
            ->whereRaw("RIGHT(REGEXP_REPLACE(telefone, '[^0-9]', ''), 11) = ?", [$sufixo])
            ->get();
    }

    private function sufixoTelefone(string $telefone): ?string
    {
        $digitos = preg_replace('/\D/', '', $telefone);

        if ($digitos === '' || strlen($digitos) < 8) {
            return null;
        }

        return substr($digitos, -11);
    }
}
