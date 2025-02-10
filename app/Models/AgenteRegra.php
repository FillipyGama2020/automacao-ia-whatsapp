<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgenteRegra extends Model
{
    protected $fillable = [
        'agente_id',
        'regra',
    ];

    public function agente()
    {
        return $this->belongsTo(Agente::class);
    }
}
