<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agentes', function (Blueprint $table) {
            $table->unsignedInteger('limite_mensagens_conversa')->nullable()->after('transferencia_automatica_fora_horario');
            $table->unsignedInteger('limite_tokens_conversa')->nullable()->after('limite_mensagens_conversa');
            $table->unsignedInteger('limite_mensagens_minuto')->nullable()->after('limite_tokens_conversa');
            $table->unsignedInteger('limite_mensagens_dia')->nullable()->after('limite_mensagens_minuto');
        });
    }

    public function down(): void
    {
        Schema::table('agentes', function (Blueprint $table) {
            $table->dropColumn(['limite_mensagens_conversa', 'limite_tokens_conversa', 'limite_mensagens_minuto', 'limite_mensagens_dia']);
        });
    }
};
