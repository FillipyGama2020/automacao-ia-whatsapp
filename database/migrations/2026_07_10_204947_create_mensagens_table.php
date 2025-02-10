<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mensagens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversa_id')->constrained('conversas')->cascadeOnDelete();
            $table->enum('remetente', ['contato', 'agente_ia', 'humano', 'sistema']);
            $table->enum('tipo', ['texto', 'imagem', 'audio', 'documento', 'video', 'template'])->default('texto');
            $table->text('conteudo')->nullable();
            $table->string('midia_path')->nullable();
            $table->string('wamid')->nullable()->unique();
            $table->unsignedInteger('tokens_prompt')->nullable();
            $table->unsignedInteger('tokens_resposta')->nullable();
            $table->enum('status_entrega', ['enviada', 'entregue', 'lida', 'falhou'])->nullable();
            $table->timestamp('enviada_em');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensagens');
    }
};
