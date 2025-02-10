<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agentes', function (Blueprint $table) {
            $table->text('prompt_horario_fechado')->nullable()->after('prompt_complementar');
            $table->text('prompt_transferencia_humano')->nullable()->after('prompt_horario_fechado');
            $table->text('prompt_despedida')->nullable()->after('prompt_transferencia_humano');
            $table->text('prompt_nao_sei_responder')->nullable()->after('prompt_despedida');
            $table->text('prompt_vendas')->nullable()->after('prompt_nao_sei_responder');
            $table->text('prompt_suporte')->nullable()->after('prompt_vendas');
        });
    }

    public function down(): void
    {
        Schema::table('agentes', function (Blueprint $table) {
            $table->dropColumn([
                'prompt_horario_fechado',
                'prompt_transferencia_humano',
                'prompt_despedida',
                'prompt_nao_sei_responder',
                'prompt_vendas',
                'prompt_suporte',
            ]);
        });
    }
};
