<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mensagens', function (Blueprint $table) {
            $table->string('modelo')->nullable()->after('tipo');
            $table->decimal('custo', 10, 6)->default(0)->after('tokens_resposta');
        });
    }

    public function down(): void
    {
        Schema::table('mensagens', function (Blueprint $table) {
            $table->dropColumn(['modelo', 'custo']);
        });
    }
};
