<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plano extends Model
{
    protected $fillable = [
        'nome',
        'descricao',
        'ativo',
        'ordem',
        'personalizado',
        'cliente_id',
        'preco_mensal',
        'preco_anual',
        'taxa_implantacao',
        'limite_conversas_mensais',
        'preco_conversa_excedente',
        'limite_agentes',
        'preco_agente_adicional',
        'permite_anexos',
        'preco_anexos_adicional',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'personalizado' => 'boolean',
            'permite_anexos' => 'boolean',
            'preco_mensal' => 'float',
            'preco_anual' => 'float',
            'taxa_implantacao' => 'float',
            'preco_conversa_excedente' => 'float',
            'preco_agente_adicional' => 'float',
            'preco_anexos_adicional' => 'float',
        ];
    }

    public function recursos()
    {
        return $this->hasMany(PlanoRecurso::class)->orderBy('ordem');
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }

    public function clienteDono()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function conversasIlimitadas(): bool
    {
        return is_null($this->limite_conversas_mensais);
    }

    public function agentesIlimitados(): bool
    {
        return is_null($this->limite_agentes);
    }
}
