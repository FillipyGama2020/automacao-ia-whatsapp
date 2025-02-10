<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgenteHorario extends Model
{
    protected $fillable = [
        'agente_id',
        'dia_semana',
        'fechado',
        'hora_inicio',
        'hora_fim',
    ];

    protected function casts(): array
    {
        return [
            'dia_semana' => 'integer',
            'fechado' => 'boolean',
        ];
    }

    public function agente()
    {
        return $this->belongsTo(Agente::class);
    }
}
