<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->unsignedInteger('ordem')->default(0);

            $table->decimal('preco_mensal', 10, 2);
            $table->decimal('preco_anual', 10, 2)->nullable();
            $table->decimal('taxa_implantacao', 10, 2)->nullable();

            $table->unsignedInteger('limite_conversas_mensais')->nullable();
            $table->decimal('preco_conversa_excedente', 10, 4)->nullable();

            $table->unsignedInteger('limite_agentes')->nullable();
            $table->decimal('preco_agente_adicional', 10, 2)->nullable();

            $table->boolean('permite_anexos')->default(true);
            $table->decimal('preco_anexos_adicional', 10, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planos');
    }
};
