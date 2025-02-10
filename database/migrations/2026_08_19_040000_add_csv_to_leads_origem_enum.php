<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE leads MODIFY COLUMN origem ENUM('whatsapp_ia', 'manual', 'csv') NOT NULL DEFAULT 'whatsapp_ia'");
    }

    public function down(): void
    {
        DB::statement("UPDATE leads SET origem = 'manual' WHERE origem = 'csv'");
        DB::statement("ALTER TABLE leads MODIFY COLUMN origem ENUM('whatsapp_ia', 'manual') NOT NULL DEFAULT 'whatsapp_ia'");
    }
};
