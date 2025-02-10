<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();

            $table->string('nome');
            $table->string('idioma', 10)->default('pt_BR');
            $table->enum('categoria', ['marketing', 'utility', 'authentication']);
            $table->text('corpo');
            $table->json('variaveis')->nullable();

            $table->enum('status', ['rascunho', 'pendente', 'aprovado', 'rejeitado', 'pausado'])->default('rascunho');
            $table->unsignedBigInteger('meta_template_id')->nullable();
            $table->string('motivo_rejeicao')->nullable();
            $table->timestamp('enviado_em')->nullable();
            $table->timestamp('aprovado_em')->nullable();

            $table->timestamps();

            $table->unique(['cliente_id', 'nome', 'idioma']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_templates');
    }
};
