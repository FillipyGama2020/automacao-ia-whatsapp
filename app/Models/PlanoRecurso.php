<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanoRecurso extends Model
{
    protected $fillable = [
        'plano_id',
        'descricao',
        'ordem',
    ];

    public function plano()
    {
        return $this->belongsTo(Plano::class);
    }
}
