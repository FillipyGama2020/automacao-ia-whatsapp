<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fechamentos_financeiros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->date('competencia');

            $table->decimal('receita_recorrente', 10, 2)->default(0);
            $table->decimal('receita_implantacao', 10, 2)->default(0);
            $table->decimal('receita_excedente', 10, 2)->default(0);

            $table->decimal('custo_ia', 10, 4)->default(0);
            $table->decimal('custo_meta', 10, 4)->default(0);
            $table->decimal('custo_infra_rateado', 10, 2)->default(0);

            $table->decimal('lucro_bruto', 10, 2)->default(0);
            $table->decimal('margem_percentual', 5, 2)->nullable();

            $table->foreignId('fechado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fechado_em');
            $table->timestamps();

            $table->unique(['cliente_id', 'competencia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fechamentos_financeiros');
    }
};
