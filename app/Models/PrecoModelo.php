<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrecoModelo extends Model
{
    protected $table = 'precos_modelo';

    private const CHAVE_COTACAO_DOLAR = 'cotacao_dolar';

    protected $fillable = [
        'modelo',
        'preco_prompt_usd_por_mil',
        'preco_resposta_usd_por_mil',
    ];

    protected function casts(): array
    {
        return [
            'preco_prompt_usd_por_mil' => 'float',
            'preco_resposta_usd_por_mil' => 'float',
        ];
    }

    public static function cotacaoDolar(): float
    {
        return (float) (Configuracao::get(self::CHAVE_COTACAO_DOLAR) ?? 0);
    }

    public static function calcularCusto(?string $modelo, int $tokensPrompt, int $tokensResposta): float
    {
        if (! $modelo) {
            return 0.0;
        }

        $preco = static::where('modelo', $modelo)->first();
        $cotacao = self::cotacaoDolar();

        if (! $preco || $cotacao <= 0) {
            return 0.0;
        }

        $custoPromptUsd = ($tokensPrompt / 1000) * $preco->preco_prompt_usd_por_mil;
        $custoRespostaUsd = ($tokensResposta / 1000) * $preco->preco_resposta_usd_por_mil;

        return round(($custoPromptUsd + $custoRespostaUsd) * $cotacao, 6);
    }
}
