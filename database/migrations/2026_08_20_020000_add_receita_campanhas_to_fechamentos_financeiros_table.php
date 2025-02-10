<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fechamentos_financeiros', function (Blueprint $table) {
            $table->decimal('receita_campanhas', 10, 2)->default(0)->after('receita_excedente');
        });
    }

    public function down(): void
    {
        Schema::table('fechamentos_financeiros', function (Blueprint $table) {
            $table->dropColumn('receita_campanhas');
        });
    }
};
