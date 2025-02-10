<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mensagens', function (Blueprint $table) {
            $table->boolean('fora_horario')->default(false)->after('status_entrega');
        });
    }

    public function down(): void
    {
        Schema::table('mensagens', function (Blueprint $table) {
            $table->dropColumn('fora_horario');
        });
    }
};
