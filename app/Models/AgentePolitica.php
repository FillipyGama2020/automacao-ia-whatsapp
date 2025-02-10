<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentePolitica extends Model
{
    protected $fillable = [
        'agente_id',
        'titulo',
        'conteudo',
    ];

    public function agente()
    {
        return $this->belongsTo(Agente::class);
    }
}
