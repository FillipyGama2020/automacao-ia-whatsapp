<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampanhaEnvio extends Model
{
    protected $fillable = ['campanha_id', 'lead_id', 'mensagem_id', 'status', 'erro'];

    public static function statusLabels(): array
    {
        return [
            'pendente' => 'Pendente',
            'enviado' => 'Enviado',
            'falhou' => 'Falhou',
        ];
    }

    public function campanha()
    {
        return $this->belongsTo(Campanha::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function mensagem()
    {
        return $this->belongsTo(Mensagem::class);
    }
}
