<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fechamentos_financeiros', function (Blueprint $table) {
            $table->decimal('valor_conversas_excedentes', 10, 2)->default(0)->after('conversas_excedentes');
            $table->decimal('valor_agentes_extras', 10, 2)->default(0)->after('agentes_extras');
            $table->decimal('valor_anexos', 10, 2)->default(0)->after('anexos_cobrados');
        });
    }

    public function down(): void
    {
        Schema::table('fechamentos_financeiros', function (Blueprint $table) {
            $table->dropColumn(['valor_conversas_excedentes', 'valor_agentes_extras', 'valor_anexos']);
        });
    }
};
