<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custos_infraestrutura', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->enum('categoria', ['vps', 'dominio', 'outros'])->default('outros');
            $table->decimal('valor_mensal', 10, 2);
            $table->boolean('ativo')->default(true);
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custos_infraestrutura');
    }
};
