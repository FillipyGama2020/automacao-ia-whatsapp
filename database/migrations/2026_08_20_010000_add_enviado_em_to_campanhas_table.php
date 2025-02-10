<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campanhas', function (Blueprint $table) {
            $table->timestamp('enviado_em')->nullable()->after('agendado_para');
        });
    }

    public function down(): void
    {
        Schema::table('campanhas', function (Blueprint $table) {
            $table->dropColumn('enviado_em');
        });
    }
};
