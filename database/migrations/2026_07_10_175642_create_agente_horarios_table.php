<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agente_horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agente_id')->constrained('agentes')->cascadeOnDelete();
            $table->unsignedTinyInteger('dia_semana');
            $table->boolean('fechado')->default(false);
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fim')->nullable();
            $table->timestamps();

            $table->unique(['agente_id', 'dia_semana']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agente_horarios');
    }
};
