<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContatoBloqueado extends Model
{
    protected $table = 'contatos_bloqueados';

    protected $fillable = [
        'cliente_id',
        'telefone',
        'nome',
        'observacoes',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
