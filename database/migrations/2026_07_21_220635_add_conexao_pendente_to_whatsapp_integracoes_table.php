<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_integracoes', function (Blueprint $table) {
            $table->text('conexao_pendente_token')->nullable()->after('access_token');
            $table->timestamp('conexao_pendente_em')->nullable()->after('conexao_pendente_token');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_integracoes', function (Blueprint $table) {
            $table->dropColumn(['conexao_pendente_token', 'conexao_pendente_em']);
        });
    }
};
