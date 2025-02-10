<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exclusoes_lgpd', function (Blueprint $table) {
            $table->unsignedInteger('quantidade_leads')->default(0)->after('quantidade_mensagens');
        });
    }

    public function down(): void
    {
        Schema::table('exclusoes_lgpd', function (Blueprint $table) {
            $table->dropColumn('quantidade_leads');
        });
    }
};
