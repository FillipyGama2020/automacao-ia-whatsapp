<?php

namespace App\Services;

use App\Models\Cliente;
use Illuminate\Http\UploadedFile;

class ImportadorContatosBloqueadosService
{
    public function importar(Cliente $cliente, UploadedFile $arquivo): array
    {
        $resumo = ['importados' => 0, 'duplicados' => 0, 'invalidos' => 0];

        $existentes = $cliente->contatosBloqueados()->pluck('telefone')
            ->map(fn ($telefone) => $this->normalizar($telefone))
            ->filter()
            ->flip();

        $handle = fopen($arquivo->getRealPath(), 'r');
        if (! $handle) {
            return $resumo;
        }

        $cabecalho = fgetcsv($handle, escape: '');
        if (! $cabecalho) {
            fclose($handle);

            return $resumo;
        }

        $indiceTelefone = $this->encontrarColuna($cabecalho, ['telefone', 'phone', 'telefone*']);
        $indiceNome = $this->encontrarColuna($cabecalho, ['nome', 'name']);

        if ($indiceTelefone === null) {
            fclose($handle);

            return $resumo;
        }

        while (($linha = fgetcsv($handle, escape: '')) !== false) {
            $telefoneOriginal = trim($linha[$indiceTelefone] ?? '');
            $nome = $indiceNome !== null ? trim($linha[$indiceNome] ?? '') : null;

            $normalizado = $this->normalizar($telefoneOriginal);

            if (! $normalizado) {
                $resumo['invalidos']++;

                continue;
            }

            if ($existentes->has($normalizado)) {
                $resumo['duplicados']++;

                continue;
            }

            $cliente->contatosBloqueados()->create([
                'telefone' => $telefoneOriginal,
                'nome' => $nome !== '' ? $nome : null,
            ]);

            $existentes->put($normalizado, true);
            $resumo['importados']++;
        }

        fclose($handle);

        return $resumo;
    }

    private function normalizar(string $telefone): ?string
    {
        $digitos = preg_replace('/\D/', '', $telefone);

        if (! $digitos || strlen($digitos) < 8) {
            return null;
        }

        return substr($digitos, -11);
    }

    private function encontrarColuna(array $cabecalho, array $nomesAceitos): ?int
    {
        foreach ($cabecalho as $indice => $coluna) {
            if (in_array(strtolower(trim($coluna)), $nomesAceitos, true)) {
                return $indice;
            }
        }

        return null;
    }
}
