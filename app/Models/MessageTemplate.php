<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    protected $fillable = [
        'cliente_id',
        'nome',
        'idioma',
        'categoria',
        'corpo',
        'variaveis',
        'status',
        'meta_template_id',
        'motivo_rejeicao',
        'enviado_em',
        'aprovado_em',
    ];

    protected function casts(): array
    {
        return [
            'variaveis' => 'array',
            'enviado_em' => 'datetime',
            'aprovado_em' => 'datetime',
        ];
    }

    public static function categoriaLabels(): array
    {
        return [
            'marketing' => 'Marketing',
            'utility' => 'Utilidade',
            'authentication' => 'Autenticação',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            'rascunho' => 'Rascunho',
            'pendente' => 'Pendente de aprovação',
            'aprovado' => 'Aprovado',
            'rejeitado' => 'Rejeitado',
            'pausado' => 'Pausado',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function campanhas()
    {
        return $this->hasMany(Campanha::class);
    }

    public function variaveisUsadas(): array
    {
        preg_match_all('/\{\{(\d+)\}\}/', $this->corpo ?? '', $matches);

        $posicoes = array_unique(array_map('intval', $matches[1]));
        sort($posicoes);

        return $posicoes;
    }
}
