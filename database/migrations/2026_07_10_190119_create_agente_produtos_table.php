<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agente_produtos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agente_id')->constrained('agentes')->cascadeOnDelete();
            $table->enum('tipo', ['produto', 'servico'])->default('produto');
            $table->string('nome');
            $table->decimal('preco', 10, 2)->nullable();
            $table->text('descricao')->nullable();
            $table->string('categoria')->nullable();
            $table->string('imagem')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agente_produtos');
    }
};
