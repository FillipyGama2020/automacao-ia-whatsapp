<?php

namespace Database\Seeders;

use App\Models\PrecoModelo;
use Illuminate\Database\Seeder;

class PrecoModeloSeeder extends Seeder
{
    public function run(): void
    {
        $precos = [
            ['modelo' => 'gpt-4o-mini', 'preco_prompt_usd_por_mil' => 0.00015, 'preco_resposta_usd_por_mil' => 0.0006],
            ['modelo' => 'gpt-4o', 'preco_prompt_usd_por_mil' => 0.0025, 'preco_resposta_usd_por_mil' => 0.01],
            ['modelo' => 'gpt-5.5', 'preco_prompt_usd_por_mil' => 0.0050, 'preco_resposta_usd_por_mil' => 0.030],
        ];

        foreach ($precos as $preco) {
            PrecoModelo::updateOrCreate(['modelo' => $preco['modelo']], $preco);
        }
    }
}
