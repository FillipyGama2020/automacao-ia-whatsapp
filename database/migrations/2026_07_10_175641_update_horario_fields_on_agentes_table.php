<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agentes', function (Blueprint $table) {
            $table->dropColumn(['horario_inicio', 'horario_fim']);
            $table->text('mensagem_fora_horario')->nullable()->after('prompt_suporte');
            $table->boolean('transferencia_automatica_fora_horario')->default(false)->after('mensagem_fora_horario');
        });
    }

    public function down(): void
    {
        Schema::table('agentes', function (Blueprint $table) {
            $table->dropColumn(['mensagem_fora_horario', 'transferencia_automatica_fora_horario']);
            $table->time('horario_inicio')->nullable();
            $table->time('horario_fim')->nullable();
        });
    }
};
