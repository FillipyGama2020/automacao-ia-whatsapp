<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('agente_id')->nullable()->constrained('agentes')->nullOnDelete();
            $table->string('contato_telefone');
            $table->string('contato_nome')->nullable();
            $table->enum('status', ['em_andamento', 'resolvida_ia', 'transferida_humano', 'abandonada'])->default('em_andamento');
            $table->string('motivo_transferencia')->nullable();
            $table->unsignedTinyInteger('avaliacao')->nullable();
            $table->unsignedInteger('tokens_prompt_total')->default(0);
            $table->unsignedInteger('tokens_resposta_total')->default(0);
            $table->decimal('custo_estimado', 10, 4)->default(0);
            $table->timestamp('iniciada_em');
            $table->timestamp('ultima_mensagem_em')->nullable();
            $table->timestamp('finalizada_em')->nullable();
            $table->timestamps();

            $table->index(['cliente_id', 'status']);
            $table->index('contato_telefone');
            $table->index('iniciada_em');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversas');
    }
};
