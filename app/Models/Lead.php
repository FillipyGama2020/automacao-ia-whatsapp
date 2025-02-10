<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'cliente_id',
        'agente_id',
        'conversa_id',
        'nome',
        'telefone',
        'email',
        'interesse',
        'classificacao',
        'status',
        'origem',
        'observacoes',
        'capturado_em',
        'aceita_campanhas',
        'opt_out_em',
    ];

    protected function casts(): array
    {
        return [
            'capturado_em' => 'datetime',
            'aceita_campanhas' => 'boolean',
            'opt_out_em' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Lead $lead) {
            $lead->capturado_em ??= now();
        });
    }

    public static function classificacaoLabels(): array
    {
        return [
            'frio' => 'Frio',
            'morno' => 'Morno',
            'quente' => 'Quente',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            'novo' => 'Novo',
            'em_contato' => 'Em contato',
            'convertido' => 'Convertido',
            'perdido' => 'Perdido',
        ];
    }

    public static function origemLabels(): array
    {
        return [
            'whatsapp_ia' => 'WhatsApp (IA)',
            'manual' => 'Manual',
            'csv' => 'Importado (CSV)',
        ];
    }

    public static function camposMapeaveisCampanha(): array
    {
        return [
            'nome' => 'Nome do lead',
            'telefone' => 'Telefone',
            'email' => 'Email',
            'interesse' => 'Interesse',
            'classificacao' => 'Classificação',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function agente()
    {
        return $this->belongsTo(Agente::class);
    }

    public function conversa()
    {
        return $this->belongsTo(Conversa::class);
    }

    public function campanhaEnvios()
    {
        return $this->hasMany(CampanhaEnvio::class);
    }
}
