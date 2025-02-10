<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agente_politicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agente_id')->constrained('agentes')->cascadeOnDelete();
            $table->string('titulo', 191);
            $table->text('conteudo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agente_politicas');
    }
};
