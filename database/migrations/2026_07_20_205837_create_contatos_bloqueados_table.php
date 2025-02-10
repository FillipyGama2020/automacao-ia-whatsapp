<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contatos_bloqueados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('telefone');
            $table->string('nome')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->index(['cliente_id', 'telefone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contatos_bloqueados');
    }
};
