<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracao extends Model
{
    protected $table = 'configuracoes';

    protected $fillable = ['chave', 'valor'];

    public static function get(string $chave, ?string $padrao = null): ?string
    {
        return static::where('chave', $chave)->value('valor') ?? $padrao;
    }

    public static function set(string $chave, string $valor): void
    {
        static::updateOrCreate(['chave' => $chave], ['valor' => $valor]);
    }
}
