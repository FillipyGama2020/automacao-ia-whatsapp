<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agentes', function (Blueprint $table) {
            $table->string('nome_ia')->nullable()->after('temperatura');
            $table->string('papel')->nullable()->after('nome_ia');
            $table->string('tom_voz')->nullable()->after('papel');
            $table->string('emojis')->default('normal')->after('tom_voz');
            $table->string('tamanho_respostas')->default('medias')->after('emojis');
            $table->string('forma_tratamento')->default('voce')->after('tamanho_respostas');
            $table->string('forma_tratamento_personalizada')->nullable()->after('forma_tratamento');
        });
    }

    public function down(): void
    {
        Schema::table('agentes', function (Blueprint $table) {
            $table->dropColumn(['nome_ia', 'papel', 'tom_voz', 'emojis', 'tamanho_respostas', 'forma_tratamento', 'forma_tratamento_personalizada']);
        });
    }
};
