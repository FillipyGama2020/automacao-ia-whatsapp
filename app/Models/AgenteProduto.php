<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgenteProduto extends Model
{
    protected $fillable = [
        'agente_id',
        'tipo',
        'nome',
        'preco',
        'descricao',
        'categoria',
        'imagem',
    ];

    protected function casts(): array
    {
        return [
            'preco' => 'float',
        ];
    }

    public function agente()
    {
        return $this->belongsTo(Agente::class);
    }
}
