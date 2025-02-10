<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_integracoes', function (Blueprint $table) {
            $table->index('cliente_id');
            $table->dropUnique(['cliente_id']);
            $table->unique('phone_number_id');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_integracoes', function (Blueprint $table) {
            $table->dropUnique(['phone_number_id']);
            $table->unique('cliente_id');
            $table->dropIndex(['cliente_id']);
        });
    }
};
