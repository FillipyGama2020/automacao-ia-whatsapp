<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plano_recursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plano_id')->constrained('planos')->cascadeOnDelete();
            $table->string('descricao', 255);
            $table->unsignedInteger('ordem')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plano_recursos');
    }
};
