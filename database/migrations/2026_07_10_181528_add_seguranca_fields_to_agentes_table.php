<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agentes', function (Blueprint $table) {
            $table->boolean('permitir_atendimento_humano')->default(true)->after('limite_mensagens_dia');
            $table->boolean('gravar_historico')->default(true)->after('permitir_atendimento_humano');
            $table->boolean('mascarar_cpf')->default(true)->after('gravar_historico');
            $table->boolean('mascarar_cartao')->default(true)->after('mascarar_cpf');
            $table->boolean('permitir_anexos')->default(true)->after('mascarar_cartao');
            $table->string('tipos_anexos_permitidos')->nullable()->after('permitir_anexos');
        });
    }

    public function down(): void
    {
        Schema::table('agentes', function (Blueprint $table) {
            $table->dropColumn(['permitir_atendimento_humano', 'gravar_historico', 'mascarar_cpf', 'mascarar_cartao', 'permitir_anexos', 'tipos_anexos_permitidos']);
        });
    }
};
