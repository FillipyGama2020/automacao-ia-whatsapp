<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('precos_modelo', function (Blueprint $table) {
            $table->renameColumn('preco_prompt_por_mil', 'preco_prompt_usd_por_mil');
            $table->renameColumn('preco_resposta_por_mil', 'preco_resposta_usd_por_mil');
        });
    }

    public function down(): void
    {
        Schema::table('precos_modelo', function (Blueprint $table) {
            $table->renameColumn('preco_prompt_usd_por_mil', 'preco_prompt_por_mil');
            $table->renameColumn('preco_resposta_usd_por_mil', 'preco_resposta_por_mil');
        });
    }
};
