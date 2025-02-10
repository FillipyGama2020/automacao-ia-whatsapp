<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('nome');
            $table->text('objetivo')->nullable();
            $table->text('prompt_principal');
            $table->text('prompt_complementar')->nullable();
            $table->string('modelo')->default('gpt-4o-mini');
            $table->decimal('temperatura', 2, 1)->default(0.7);
            $table->time('horario_inicio')->nullable();
            $table->time('horario_fim')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agentes');
    }
};
