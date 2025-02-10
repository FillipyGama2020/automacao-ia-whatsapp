<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgenteFaq extends Model
{
    protected $fillable = [
        'agente_id',
        'pergunta',
        'resposta',
    ];

    public function agente()
    {
        return $this->belongsTo(Agente::class);
    }
}
