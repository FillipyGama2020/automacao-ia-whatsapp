<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE mensagens MODIFY COLUMN remetente ENUM('contato', 'agente_ia', 'humano', 'sistema', 'campanha') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("UPDATE mensagens SET remetente = 'sistema' WHERE remetente = 'campanha'");
        DB::statement("ALTER TABLE mensagens MODIFY COLUMN remetente ENUM('contato', 'agente_ia', 'humano', 'sistema') NOT NULL");
    }
};
