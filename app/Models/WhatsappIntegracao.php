<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WhatsappIntegracao extends Model
{
    protected $table = 'whatsapp_integracoes';

    protected $fillable = [
        'cliente_id',
        'app_id',
        'app_secret',
        'business_account_id',
        'phone_number_id',
        'access_token',
        'status',
        'last_checked_at',
        'last_error',
        'conexao_pendente_token',
        'conexao_pendente_em',
        'modo_equipe_agentes',
        'prompt_classificacao_extra',
    ];

    protected function casts(): array
    {
        return [
            'app_secret' => 'encrypted',
            'access_token' => 'encrypted',
            'conexao_pendente_token' => 'encrypted',
            'last_checked_at' => 'datetime',
            'api_token_gerado_em' => 'datetime',
            'conexao_pendente_em' => 'datetime',
            'modo_equipe_agentes' => 'boolean',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function gerarNovoApiToken(): string
    {
        $token = Str::random(48);

        $this->forceFill([
            'api_token' => hash('sha256', $token),
            'api_token_gerado_em' => now(),
        ])->save();

        return $token;
    }

    public static function encontrarPorApiToken(string $token): ?self
    {
        return static::where('api_token', hash('sha256', $token))->first();
    }
}
