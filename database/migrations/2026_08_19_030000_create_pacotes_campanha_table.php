<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pacotes_campanha', function (Blueprint $table) {
            $table->id();
            $table->enum('categoria', ['marketing', 'utility', 'authentication']);
            $table->unsignedInteger('quantidade');
            $table->decimal('preco', 10, 2);
            $table->timestamps();
            $table->unique(['categoria', 'quantidade']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pacotes_campanha');
    }
};
