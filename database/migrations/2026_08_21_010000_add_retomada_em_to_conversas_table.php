<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversas', function (Blueprint $table) {
            $table->timestamp('retomada_em')->nullable()->after('finalizada_em');
        });
    }

    public function down(): void
    {
        Schema::table('conversas', function (Blueprint $table) {
            $table->dropColumn('retomada_em');
        });
    }
};
