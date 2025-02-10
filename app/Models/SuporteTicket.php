<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuporteTicket extends Model
{
    protected $fillable = [
        'cliente_id',
        'aberto_por_id',
        'assunto',
        'status',
    ];

    public static function statusLabels(): array
    {
        return [
            'aberto' => 'Aberto',
            'respondido' => 'Respondido',
            'fechado' => 'Fechado',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function abertoPor()
    {
        return $this->belongsTo(User::class, 'aberto_por_id');
    }

    public function mensagens()
    {
        return $this->hasMany(SuporteMensagem::class, 'ticket_id')->orderBy('created_at');
    }
}
