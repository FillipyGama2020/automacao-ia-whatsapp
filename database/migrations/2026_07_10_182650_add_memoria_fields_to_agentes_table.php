<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agentes', function (Blueprint $table) {
            $table->boolean('memoria_habilitada')->default(true)->after('tipos_anexos_permitidos');
            $table->unsignedSmallInteger('memoria_dias_lembrar')->default(30)->after('memoria_habilitada');
            $table->boolean('memoria_resumo_automatico')->default(true)->after('memoria_dias_lembrar');
            $table->boolean('memoria_salvar_preferencias')->default(true)->after('memoria_resumo_automatico');
            $table->boolean('memoria_salvar_nome')->default(true)->after('memoria_salvar_preferencias');
            $table->boolean('memoria_salvar_endereco')->default(false)->after('memoria_salvar_nome');
        });
    }

    public function down(): void
    {
        Schema::table('agentes', function (Blueprint $table) {
            $table->dropColumn(['memoria_habilitada', 'memoria_dias_lembrar', 'memoria_resumo_automatico', 'memoria_salvar_preferencias', 'memoria_salvar_nome', 'memoria_salvar_endereco']);
        });
    }
};
