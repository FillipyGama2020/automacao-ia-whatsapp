<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agente_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agente_id')->constrained('agentes')->cascadeOnDelete();
            $table->enum('tipo', ['arquivo', 'link'])->default('arquivo');
            $table->string('nome');
            $table->string('arquivo')->nullable();
            $table->string('url')->nullable();
            $table->text('descricao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agente_documentos');
    }
};
