<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_integracoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->unique()->constrained('clientes')->cascadeOnDelete();
            $table->string('app_id')->nullable();
            $table->text('app_secret')->nullable();
            $table->string('business_account_id')->nullable();
            $table->string('phone_number_id')->nullable();
            $table->text('access_token')->nullable();
            $table->enum('status', ['nao_conectado', 'conectado', 'erro'])->default('nao_conectado');
            $table->timestamp('last_checked_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_integracoes');
    }
};
