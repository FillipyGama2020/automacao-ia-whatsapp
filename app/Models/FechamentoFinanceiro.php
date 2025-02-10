<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FechamentoFinanceiro extends Model
{
    protected $table = 'fechamentos_financeiros';

    protected $fillable = [
        'cliente_id',
        'competencia',
        'receita_recorrente',
        'receita_implantacao',
        'receita_excedente',
        'receita_campanhas',
        'conversas_no_mes',
        'limite_conversas_plano',
        'conversas_excedentes',
        'valor_conversas_excedentes',
        'agentes_no_mes',
        'limite_agentes_plano',
        'agentes_extras',
        'valor_agentes_extras',
        'anexos_cobrados',
        'valor_anexos',
        'custo_ia',
        'custo_meta',
        'custo_infra_rateado',
        'lucro_bruto',
        'margem_percentual',
        'fechado_por_id',
        'fechado_em',
    ];

    protected function casts(): array
    {
        return [
            'competencia' => 'date',
            'receita_recorrente' => 'float',
            'receita_implantacao' => 'float',
            'receita_excedente' => 'float',
            'receita_campanhas' => 'float',
            'valor_conversas_excedentes' => 'float',
            'valor_agentes_extras' => 'float',
            'anexos_cobrados' => 'boolean',
            'valor_anexos' => 'float',
            'custo_ia' => 'float',
            'custo_meta' => 'float',
            'custo_infra_rateado' => 'float',
            'lucro_bruto' => 'float',
            'margem_percentual' => 'float',
            'fechado_em' => 'datetime',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function fechadoPor()
    {
        return $this->belongsTo(User::class, 'fechado_por_id');
    }

    public function receitaTotal(): float
    {
        return $this->receita_recorrente + $this->receita_implantacao + $this->receita_excedente + $this->receita_campanhas;
    }

    public function custoTotal(): float
    {
        return $this->custo_ia + $this->custo_meta + $this->custo_infra_rateado;
    }

    public function percentualConversas(): ?float
    {
        if (! $this->limite_conversas_plano) {
            return null;
        }

        return round(($this->conversas_no_mes / $this->limite_conversas_plano) * 100, 1);
    }

    public function percentualAgentes(): ?float
    {
        if (! $this->limite_agentes_plano) {
            return null;
        }

        return round(($this->agentes_no_mes / $this->limite_agentes_plano) * 100, 1);
    }
}
