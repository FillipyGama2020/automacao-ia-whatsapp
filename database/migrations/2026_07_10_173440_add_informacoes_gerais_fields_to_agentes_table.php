<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agentes', function (Blueprint $table) {
            $table->text('descricao_interna')->nullable()->after('objetivo');
            $table->string('departamento')->nullable()->after('descricao_interna');
            $table->string('idioma')->default('pt-BR')->after('departamento');
            $table->string('timezone')->default('America/Sao_Paulo')->after('idioma');
            $table->string('avatar')->nullable()->after('timezone');
            $table->string('cor', 7)->default('#6366f1')->after('avatar');
        });
    }

    public function down(): void
    {
        Schema::table('agentes', function (Blueprint $table) {
            $table->dropColumn(['descricao_interna', 'departamento', 'idioma', 'timezone', 'avatar', 'cor']);
        });
    }
};
