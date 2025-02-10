<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgenteFeriado extends Model
{
    protected $fillable = [
        'agente_id',
        'data',
        'descricao',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'date',
        ];
    }

    public function agente()
    {
        return $this->belongsTo(Agente::class);
    }
}
