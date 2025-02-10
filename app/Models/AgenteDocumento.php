<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgenteDocumento extends Model
{
    protected $fillable = [
        'agente_id',
        'tipo',
        'nome',
        'arquivo',
        'url',
        'descricao',
    ];

    public function agente()
    {
        return $this->belongsTo(Agente::class);
    }
}
