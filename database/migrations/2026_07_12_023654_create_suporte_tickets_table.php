<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suporte_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('aberto_por_id')->constrained('users')->cascadeOnDelete();
            $table->string('assunto');
            $table->enum('status', ['aberto', 'respondido', 'fechado'])->default('aberto');
            $table->timestamps();

            $table->index(['cliente_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suporte_tickets');
    }
};
