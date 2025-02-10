<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exclusoes_lgpd', function (Blueprint $table) {
            $table->id();
            $table->string('contato_telefone')->nullable();
            $table->enum('motivo', ['retencao_automatica', 'solicitacao_titular'])->default('solicitacao_titular');
            $table->unsignedInteger('quantidade_conversas');
            $table->unsignedInteger('quantidade_mensagens');
            $table->foreignId('executado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('executado_em');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exclusoes_lgpd');
    }
};
