<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_integracoes', function (Blueprint $table) {
            $table->boolean('modo_equipe_agentes')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_integracoes', function (Blueprint $table) {
            $table->dropColumn('modo_equipe_agentes');
        });
    }
};
