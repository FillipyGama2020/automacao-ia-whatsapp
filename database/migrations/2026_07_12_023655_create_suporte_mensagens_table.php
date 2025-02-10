<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suporte_mensagens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('suporte_tickets')->cascadeOnDelete();
            $table->foreignId('autor_id')->constrained('users')->cascadeOnDelete();
            $table->enum('remetente', ['cliente', 'admin']);
            $table->text('mensagem');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suporte_mensagens');
    }
};
