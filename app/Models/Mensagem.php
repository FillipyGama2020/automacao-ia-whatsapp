<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mensagem extends Model
{
    protected $table = 'mensagens';

    protected $fillable = [
        'conversa_id',
        'remetente',
        'tipo',
        'conteudo',
        'modelo',
        'midia_path',
        'midia_texto_extraido',
        'wamid',
        'tokens_prompt',
        'tokens_resposta',
        'custo',
        'custo_meta',
        'status_entrega',
        'fora_horario',
        'enviada_em',
    ];

    protected function casts(): array
    {
        return [
            'enviada_em' => 'datetime',
            'fora_horario' => 'boolean',
        ];
    }

    public static function tipoLabels(): array
    {
        return [
            'texto' => 'Texto',
            'imagem' => 'Imagem',
            'audio' => 'Áudio',
            'documento' => 'Documento',
            'video' => 'Vídeo',
            'template' => 'Template',
        ];
    }

    public static function remetenteLabels(): array
    {
        return [
            'contato' => 'Contato',
            'agente_ia' => 'Agente IA',
            'humano' => 'Humano',
            'sistema' => 'Sistema',
            'campanha' => 'Campanha',
        ];
    }

    public function conversa()
    {
        return $this->belongsTo(Conversa::class);
    }
}
