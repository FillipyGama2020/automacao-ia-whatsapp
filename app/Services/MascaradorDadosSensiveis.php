<?php

namespace App\Services;

class MascaradorDadosSensiveis
{
    public static function mascarar(string $texto, bool $mascararCpf = true, bool $mascararCartao = true): string
    {
        if ($mascararCpf) {
            $texto = preg_replace('/\b\d{3}\.\d{3}\.\d{3}-\d{2}\b/', '***.***.***-**', $texto);

            $texto = preg_replace('/(?<!\d)\d{11}(?!\d)/', '***.***.***-**', $texto);
        }

        if ($mascararCartao) {
            $texto = preg_replace_callback(
                '/(?<!\d)\d{1,6}(?:[ -]\d{1,6}){2,5}(?!\d)/',
                fn (array $m) => self::pareceCartao($m[0]) ? '**** **** **** ****' : $m[0],
                $texto
            );

            $texto = preg_replace_callback(
                '/(?<!\d)\d{13,19}(?!\d)/',
                fn (array $m) => self::pareceCartao($m[0]) ? '**** **** **** ****' : $m[0],
                $texto
            );
        }

        return $texto;
    }

    private static function pareceCartao(string $valor): bool
    {
        $digitos = preg_replace('/\D/', '', $valor);

        if (strlen($digitos) < 13 || strlen($digitos) > 19) {
            return false;
        }

        return self::passaNoChecksumLuhn($digitos);
    }

    private static function passaNoChecksumLuhn(string $digitos): bool
    {
        $soma = 0;
        $alternar = false;

        for ($i = strlen($digitos) - 1; $i >= 0; $i--) {
            $n = (int) $digitos[$i];

            if ($alternar) {
                $n *= 2;
                if ($n > 9) {
                    $n -= 9;
                }
            }

            $soma += $n;
            $alternar = ! $alternar;
        }

        return $soma % 10 === 0;
    }
}
