<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->boolean('aceita_campanhas')->default(true)->after('observacoes');
            $table->timestamp('opt_out_em')->nullable()->after('aceita_campanhas');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['aceita_campanhas', 'opt_out_em']);
        });
    }
};
