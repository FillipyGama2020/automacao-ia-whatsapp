<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'nome_empresa',
        'cnpj',
        'responsavel',
        'telefone',
        'email',
        'endereco',
        'site',
        'observacoes',
        'status',
        'plano_id',
        'mensagens_proativas_habilitado',
        'leads_portal_habilitado',
    ];

    protected function casts(): array
    {
        return [
            'mensagens_proativas_habilitado' => 'boolean',
            'leads_portal_habilitado' => 'boolean',
        ];
    }

    public function whatsappIntegracao()
    {
        return $this->hasOne(WhatsappIntegracao::class);
    }

    public function whatsappIntegracoes()
    {
        return $this->hasMany(WhatsappIntegracao::class);
    }

    public function plano()
    {
        return $this->belongsTo(Plano::class);
    }

    public function agentes()
    {
        return $this->hasMany(Agente::class);
    }

    public function conversas()
    {
        return $this->hasMany(Conversa::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function contatosBloqueados()
    {
        return $this->hasMany(ContatoBloqueado::class);
    }

    public function campanhas()
    {
        return $this->hasMany(Campanha::class);
    }

    public function fechamentosFinanceiros()
    {
        return $this->hasMany(FechamentoFinanceiro::class);
    }

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    public function suporteTickets()
    {
        return $this->hasMany(SuporteTicket::class);
    }

    public function limiteConversasMensaisInfo(?int $atual = null): array
    {
        $this->loadMissing('plano');

        $limite = $this->plano->limite_conversas_mensais ?? null;

        $atual ??= $this->conversas()
            ->whereBetween('iniciada_em', [now()->startOfMonth(), now()->endOfMonth()])
            ->where('custo_estimado', '>', 0)
            ->count();

        return [
            'limite' => $limite,
            'atual' => $atual,
            'excedido' => $limite !== null && $atual > $limite,
        ];
    }
}
