<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustoInfraestrutura extends Model
{
    protected $table = 'custos_infraestrutura';

    protected $fillable = [
        'descricao',
        'categoria',
        'valor_mensal',
        'ativo',
        'data_inicio',
        'data_fim',
    ];

    protected function casts(): array
    {
        return [
            'valor_mensal' => 'float',
            'ativo' => 'boolean',
            'data_inicio' => 'date',
            'data_fim' => 'date',
        ];
    }

    public static function categoriaLabels(): array
    {
        return [
            'vps' => 'VPS',
            'dominio' => 'Domínio',
            'outros' => 'Outros',
        ];
    }

    public static function totalVigenteEm(\DateTimeInterface $competencia): float
    {
        $inicioMes = \Illuminate\Support\Carbon::parse($competencia)->startOfMonth();
        $fimMes = \Illuminate\Support\Carbon::parse($competencia)->endOfMonth();

        return (float) static::where('ativo', true)
            ->where('data_inicio', '<=', $fimMes)
            ->where(function ($q) use ($inicioMes) {
                $q->whereNull('data_fim')->orWhere('data_fim', '>=', $inicioMes);
            })
            ->sum('valor_mensal');
    }
}
