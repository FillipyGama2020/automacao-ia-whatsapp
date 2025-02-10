<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fechamentos_financeiros', function (Blueprint $table) {
            $table->unsignedInteger('conversas_no_mes')->nullable()->after('receita_excedente');
            $table->unsignedInteger('limite_conversas_plano')->nullable()->after('conversas_no_mes');
            $table->unsignedInteger('conversas_excedentes')->default(0)->after('limite_conversas_plano');

            $table->unsignedInteger('agentes_no_mes')->nullable()->after('conversas_excedentes');
            $table->unsignedInteger('limite_agentes_plano')->nullable()->after('agentes_no_mes');
            $table->unsignedInteger('agentes_extras')->default(0)->after('limite_agentes_plano');

            $table->boolean('anexos_cobrados')->default(false)->after('agentes_extras');
        });
    }

    public function down(): void
    {
        Schema::table('fechamentos_financeiros', function (Blueprint $table) {
            $table->dropColumn([
                'conversas_no_mes', 'limite_conversas_plano', 'conversas_excedentes',
                'agentes_no_mes', 'limite_agentes_plano', 'agentes_extras', 'anexos_cobrados',
            ]);
        });
    }
};
