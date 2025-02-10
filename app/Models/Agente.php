<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agente extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'cliente_id',
        'whatsapp_integracao_id',
        'nome',
        'objetivo',
        'descricao_interna',
        'departamento',
        'idioma',
        'timezone',
        'avatar',
        'cor',
        'prompt_principal',
        'prompt_complementar',
        'prompt_horario_fechado',
        'prompt_transferencia_humano',
        'prompt_despedida',
        'enviar_link_avaliacao',
        'prompt_nao_sei_responder',
        'prompt_vendas',
        'prompt_suporte',
        'modelo',
        'top_p',
        'frequency_penalty',
        'presence_penalty',
        'max_tokens',
        'timeout',
        'modelo_fallback',
        'temperatura',
        'nome_ia',
        'papel',
        'tom_voz',
        'emojis',
        'tamanho_respostas',
        'forma_tratamento',
        'forma_tratamento_personalizada',
        'mensagem_fora_horario',
        'transferencia_automatica_fora_horario',
        'retomar_ao_abrir_horario',
        'limite_mensagens_conversa',
        'limite_tokens_conversa',
        'prompt_limite_atingido',
        'limite_mensagens_minuto',
        'limite_mensagens_dia',
        'retomada_automatica_minutos',
        'prompt_retomada_automatica',
        'permitir_atendimento_humano',
        'mascarar_cpf',
        'mascarar_cartao',
        'permitir_anexos',
        'tipos_anexos_permitidos',
        'memoria_habilitada',
        'memoria_dias_lembrar',
        'memoria_resumo_automatico',
        'memoria_salvar_preferencias',
        'memoria_salvar_nome',
        'memoria_salvar_endereco',
        'ferramentas_habilitadas',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'temperatura' => 'float',
            'top_p' => 'float',
            'frequency_penalty' => 'float',
            'presence_penalty' => 'float',
            'ativo' => 'boolean',
            'enviar_link_avaliacao' => 'boolean',
            'transferencia_automatica_fora_horario' => 'boolean',
            'retomar_ao_abrir_horario' => 'boolean',
            'permitir_atendimento_humano' => 'boolean',
            'mascarar_cpf' => 'boolean',
            'mascarar_cartao' => 'boolean',
            'permitir_anexos' => 'boolean',
            'memoria_habilitada' => 'boolean',
            'memoria_resumo_automatico' => 'boolean',
            'memoria_salvar_preferencias' => 'boolean',
            'memoria_salvar_nome' => 'boolean',
            'memoria_salvar_endereco' => 'boolean',
        ];
    }

    public static function diasSemana(): array
    {
        return [
            1 => 'Segunda',
            2 => 'Terça',
            3 => 'Quarta',
            4 => 'Quinta',
            5 => 'Sexta',
            6 => 'Sábado',
            0 => 'Domingo',
        ];
    }

    public static function ferramentasDisponiveis(): array
    {
        return [
            'estoque' => 'Consultar estoque',
            'pedidos' => 'Consultar pedidos',
            'erp' => 'Consultar ERP',
            'google_agenda' => 'Google Agenda',
            'crm' => 'CRM',
            'emitir_boleto' => 'Emitir boleto',
            'buscar_cpf' => 'Buscar CPF',
            'criar_orcamento' => 'Criar orçamento',
            'abrir_chamado' => 'Abrir chamado',
            'agendar_consulta' => 'Agendar consulta',
            'consultar_financeiro' => 'Consultar financeiro',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function whatsappIntegracao()
    {
        return $this->belongsTo(WhatsappIntegracao::class);
    }

    public function horarios()
    {
        return $this->hasMany(AgenteHorario::class);
    }

    public function feriados()
    {
        return $this->hasMany(AgenteFeriado::class)->orderBy('data');
    }

    public function regras()
    {
        return $this->hasMany(AgenteRegra::class);
    }

    public function faqs()
    {
        return $this->hasMany(AgenteFaq::class);
    }

    public function produtos()
    {
        return $this->hasMany(AgenteProduto::class);
    }

    public function politicas()
    {
        return $this->hasMany(AgentePolitica::class);
    }

    public function documentos()
    {
        return $this->hasMany(AgenteDocumento::class);
    }

    public function conversas()
    {
        return $this->hasMany(Conversa::class);
    }

    public function estaAberto(): bool
    {
        $this->loadMissing('horarios', 'feriados');

        $agora = now($this->timezone ?: 'America/Sao_Paulo');
        $hoje = $agora->toDateString();

        $feriado = $this->feriados->first(fn ($f) => $f->data->toDateString() === $hoje);
        if ($feriado) {
            return false;
        }

        $horarioHoje = $this->horarios->firstWhere('dia_semana', $agora->dayOfWeek);
        if (! $horarioHoje || $horarioHoje->fechado) {
            return false;
        }

        if (! $horarioHoje->hora_inicio || ! $horarioHoje->hora_fim) {
            return true;
        }

        $horaAtual = $agora->format('H:i:s');

        return $horaAtual >= $horarioHoje->hora_inicio && $horaAtual <= $horarioHoje->hora_fim;
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }
}
