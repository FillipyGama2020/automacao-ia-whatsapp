<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campanhas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('message_template_id')->constrained('message_templates');
            $table->foreignId('criado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('tipo_destinatario', ['individual', 'lote']);
            $table->enum('filtro_lote', ['todos', 'quente', 'convertido'])->nullable();
            $table->json('variaveis_mapeamento')->nullable();
            $table->timestamp('agendado_para')->nullable();
            $table->enum('status', ['rascunho', 'agendada', 'enviando', 'concluida', 'cancelada'])->default('rascunho');
            $table->unsignedInteger('total_leads')->default(0);
            $table->decimal('custo_meta_total', 10, 4)->default(0);
            $table->decimal('valor_cobrado', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('campanha_envios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campanha_id')->constrained('campanhas')->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('mensagem_id')->nullable()->constrained('mensagens')->nullOnDelete();
            $table->enum('status', ['pendente', 'enviado', 'falhou'])->default('pendente');
            $table->string('erro')->nullable();
            $table->timestamps();

            $table->unique(['campanha_id', 'lead_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campanha_envios');
        Schema::dropIfExists('campanhas');
    }
};
