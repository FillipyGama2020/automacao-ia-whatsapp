<?php

namespace App\Console\Commands;

use App\Models\Configuracao;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

#[Signature('app:atualizar-cotacao-dolar')]
#[Description('Busca a cotação de venda do dólar (PTAX) no Banco Central e salva em configuracoes')]
class AtualizarCotacaoDolar extends Command
{
    public function handle(): int
    {
        for ($diasAtras = 0; $diasAtras <= 7; $diasAtras++) {
            $data = now()->subDays($diasAtras)->format('m-d-Y');

            try {
                $response = Http::timeout(10)->get(
                    "https://olinda.bcb.gov.br/olinda/servico/PTAX/versao/v1/odata/CotacaoDolarDia(dataCotacao='{$data}')",
                    ['$format' => 'json']
                );
            } catch (\Throwable $e) {
                $this->error('Falha ao conectar com a API do Banco Central: '.$e->getMessage());

                return self::FAILURE;
            }

            if (! $response->successful()) {
                continue;
            }

            $cotacao = $response->json('value.0.cotacaoVenda');

            if ($cotacao) {
                Configuracao::set('cotacao_dolar', (string) $cotacao);
                Configuracao::set('cotacao_dolar_atualizado_em', now()->toDateTimeString());

                $this->info("Cotação do dólar atualizada: R$ {$cotacao} (referente a {$data}).");

                return self::SUCCESS;
            }
        }

        $this->warn('Não foi possível obter a cotação do dólar nos últimos dias.');

        return self::FAILURE;
    }
}
