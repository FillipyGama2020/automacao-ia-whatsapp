<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('precos_modelo', function (Blueprint $table) {
            $table->id();
            $table->string('modelo')->unique();
            $table->decimal('preco_prompt_por_mil', 10, 6)->default(0);
            $table->decimal('preco_resposta_por_mil', 10, 6)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('precos_modelo');
    }
};
