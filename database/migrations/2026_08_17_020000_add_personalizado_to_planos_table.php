<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planos', function (Blueprint $table) {
            $table->boolean('personalizado')->default(false)->after('ordem');
            $table->foreignId('cliente_id')->nullable()->unique()->after('personalizado')
                ->constrained('clientes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('planos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cliente_id');
            $table->dropColumn('personalizado');
        });
    }
};
