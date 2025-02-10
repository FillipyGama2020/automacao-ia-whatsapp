<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversas', function (Blueprint $table) {
            $table->foreignId('whatsapp_integracao_id')->nullable()->after('agente_id')
                ->constrained('whatsapp_integracoes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('conversas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('whatsapp_integracao_id');
        });
    }
};
