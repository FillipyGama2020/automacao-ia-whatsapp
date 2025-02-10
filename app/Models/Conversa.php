<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Conversa extends Model
{
    protected $fillable = [
        'cliente_id',
        'agente_id',
        'whatsapp_integracao_id',
        'contato_telefone',
        'contato_nome',
        'status',
        'motivo_transferencia',
        'avaliacao',
        'token_avaliacao',
        'avaliada_em',
        'tokens_prompt_total',
        'tokens_resposta_total',
        'custo_estimado',
        'iniciada_em',
        'ultima_mensagem_em',
        'finalizada_em',
        'retomada_em',
    ];

    protected function casts(): array
    {
        return [
            'custo_estimado' => 'float',
            'iniciada_em' => 'datetime',
            'ultima_mensagem_em' => 'datetime',
            'finalizada_em' => 'datetime',
            'avaliada_em' => 'datetime',
            'retomada_em' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Conversa $conversa) {
            $conversa->token_avaliacao ??= Str::random(32);
        });
    }

    public function linkAvaliacao(): string
    {
        return route('avaliacao.show', $this->token_avaliacao);
    }

    public static function statusLabels(): array
    {
        return [
            'em_andamento' => 'Em andamento',
            'resolvida_ia' => 'Resolvida pela IA',
            'transferida_humano' => 'Transferida para humano',
            'abandonada' => 'Abandonada',
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

    public function whatsappIntegracao()
    {
        return $this->belongsTo(WhatsappIntegracao::class);
    }

    public function mensagens()
    {
        return $this->hasMany(Mensagem::class)->orderBy('enviada_em');
    }

    public function lead()
    {
        return $this->hasOne(Lead::class);
    }
}
