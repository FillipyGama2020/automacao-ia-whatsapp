<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campanha extends Model
{
    protected $fillable = [
        'cliente_id',
        'message_template_id',
        'criado_por',
        'tipo_destinatario',
        'filtro_lote',
        'variaveis_mapeamento',
        'agendado_para',
        'enviado_em',
        'status',
        'total_leads',
        'custo_meta_total',
        'valor_cobrado',
    ];

    protected function casts(): array
    {
        return [
            'variaveis_mapeamento' => 'array',
            'agendado_para' => 'datetime',
            'enviado_em' => 'datetime',
            'custo_meta_total' => 'decimal:4',
            'valor_cobrado' => 'decimal:2',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            'rascunho' => 'Rascunho',
            'agendada' => 'Agendada',
            'enviando' => 'Enviando',
            'concluida' => 'Concluída',
            'cancelada' => 'Cancelada',
        ];
    }

    public static function filtroLoteLabels(): array
    {
        return [
            'todos' => 'Todos os leads',
            'quente' => 'Leads quentes',
            'convertido' => 'Leads convertidos',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function messageTemplate()
    {
        return $this->belongsTo(MessageTemplate::class);
    }

    public function criadoPor()
    {
        return $this->belongsTo(User::class, 'criado_por');
    }

    public function envios()
    {
        return $this->hasMany(CampanhaEnvio::class);
    }
}
