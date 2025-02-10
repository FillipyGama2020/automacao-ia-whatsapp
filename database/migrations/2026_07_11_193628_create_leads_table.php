<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('agente_id')->nullable()->constrained('agentes')->nullOnDelete();
            $table->foreignId('conversa_id')->nullable()->constrained('conversas')->nullOnDelete();
            $table->string('nome')->nullable();
            $table->string('telefone');
            $table->string('email')->nullable();
            $table->text('interesse')->nullable();
            $table->enum('classificacao', ['frio', 'morno', 'quente'])->nullable();
            $table->enum('status', ['novo', 'em_contato', 'convertido', 'perdido'])->default('novo');
            $table->enum('origem', ['whatsapp_ia', 'manual'])->default('whatsapp_ia');
            $table->text('observacoes')->nullable();
            $table->timestamp('capturado_em');
            $table->timestamps();

            $table->unique(['cliente_id', 'telefone']);
            $table->index(['cliente_id', 'status']);
            $table->index(['cliente_id', 'classificacao']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
