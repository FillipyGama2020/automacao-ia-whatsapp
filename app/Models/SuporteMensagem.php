<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuporteMensagem extends Model
{
    protected $table = 'suporte_mensagens';

    protected $fillable = [
        'ticket_id',
        'autor_id',
        'remetente',
        'mensagem',
    ];

    public function ticket()
    {
        return $this->belongsTo(SuporteTicket::class, 'ticket_id');
    }

    public function autor()
    {
        return $this->belongsTo(User::class, 'autor_id');
    }
}
