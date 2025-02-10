<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agentes', function (Blueprint $table) {
            $table->string('ferramentas_habilitadas')->nullable()->after('memoria_salvar_endereco');
        });
    }

    public function down(): void
    {
        Schema::table('agentes', function (Blueprint $table) {
            $table->dropColumn('ferramentas_habilitadas');
        });
    }
};
