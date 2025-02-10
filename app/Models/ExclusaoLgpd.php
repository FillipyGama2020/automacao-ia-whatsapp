<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExclusaoLgpd extends Model
{
    protected $table = 'exclusoes_lgpd';

    protected $fillable = [
        'contato_telefone',
        'motivo',
        'quantidade_conversas',
        'quantidade_mensagens',
        'quantidade_leads',
        'executado_por_id',
        'executado_em',
    ];

    protected function casts(): array
    {
        return [
            'executado_em' => 'datetime',
        ];
    }

    public function executadoPor()
    {
        return $this->belongsTo(User::class, 'executado_por_id');
    }
}
