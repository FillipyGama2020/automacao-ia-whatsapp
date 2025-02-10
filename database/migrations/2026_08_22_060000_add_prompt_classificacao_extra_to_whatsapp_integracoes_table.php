<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_integracoes', function (Blueprint $table) {
            $table->text('prompt_classificacao_extra')->nullable()->after('modo_equipe_agentes');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_integracoes', function (Blueprint $table) {
            $table->dropColumn('prompt_classificacao_extra');
        });
    }
};
