<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversas', function (Blueprint $table) {
            $table->string('token_avaliacao', 40)->nullable()->unique()->after('avaliacao');
            $table->timestamp('avaliada_em')->nullable()->after('token_avaliacao');
        });
    }

    public function down(): void
    {
        Schema::table('conversas', function (Blueprint $table) {
            $table->dropColumn(['token_avaliacao', 'avaliada_em']);
        });
    }
};
