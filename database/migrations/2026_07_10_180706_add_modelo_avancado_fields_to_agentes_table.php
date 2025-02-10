<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agentes', function (Blueprint $table) {
            $table->decimal('top_p', 3, 2)->default(1)->after('modelo');
            $table->decimal('frequency_penalty', 3, 2)->default(0)->after('top_p');
            $table->decimal('presence_penalty', 3, 2)->default(0)->after('frequency_penalty');
            $table->unsignedInteger('max_tokens')->nullable()->after('presence_penalty');
            $table->unsignedInteger('timeout')->default(30)->after('max_tokens');
            $table->string('modelo_fallback')->nullable()->after('timeout');
        });
    }

    public function down(): void
    {
        Schema::table('agentes', function (Blueprint $table) {
            $table->dropColumn(['top_p', 'frequency_penalty', 'presence_penalty', 'max_tokens', 'timeout', 'modelo_fallback']);
        });
    }
};
