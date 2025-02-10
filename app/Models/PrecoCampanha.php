<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrecoCampanha extends Model
{
    protected $table = 'precos_campanha';

    protected $fillable = ['categoria', 'preco_por_lead'];

    protected function casts(): array
    {
        return [
            'preco_por_lead' => 'decimal:2',
        ];
    }
}
