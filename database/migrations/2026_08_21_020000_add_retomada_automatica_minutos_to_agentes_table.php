<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agentes', function (Blueprint $table) {
            $table->unsignedInteger('retomada_automatica_minutos')->nullable()->after('limite_mensagens_dia');
        });
    }

    public function down(): void
    {
        Schema::table('agentes', function (Blueprint $table) {
            $table->dropColumn('retomada_automatica_minutos');
        });
    }
};
