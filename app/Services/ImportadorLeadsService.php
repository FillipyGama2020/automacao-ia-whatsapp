<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Lead;
use Illuminate\Http\UploadedFile;

class ImportadorLeadsService
{
    public function importar(Cliente $cliente, UploadedFile $arquivo): array
    {
        $resumo = ['importados' => 0, 'duplicados' => 0, 'invalidos' => 0];

        $existentes = $cliente->leads()->pluck('telefone')
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
        $indiceEmail = $this->encontrarColuna($cabecalho, ['email', 'e-mail']);
        $indiceInteresse = $this->encontrarColuna($cabecalho, ['interesse', 'interest']);
        $indiceClassificacao = $this->encontrarColuna($cabecalho, ['classificacao', 'classificação']);

        if ($indiceTelefone === null) {
            fclose($handle);

            return $resumo;
        }

        $classificacoesValidas = array_keys(Lead::classificacaoLabels());

        while (($linha = fgetcsv($handle, escape: '')) !== false) {
            $telefoneOriginal = trim($linha[$indiceTelefone] ?? '');
            $normalizado = $this->normalizar($telefoneOriginal);

            if (! $normalizado) {
                $resumo['invalidos']++;

                continue;
            }

            if ($existentes->has($normalizado)) {
                $resumo['duplicados']++;

                continue;
            }

            $nome = $indiceNome !== null ? trim($linha[$indiceNome] ?? '') : null;
            $email = $indiceEmail !== null ? trim($linha[$indiceEmail] ?? '') : null;
            $email = $email && filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
            $interesse = $indiceInteresse !== null ? trim($linha[$indiceInteresse] ?? '') : null;
            $classificacao = $indiceClassificacao !== null ? strtolower(trim($linha[$indiceClassificacao] ?? '')) : null;

            $cliente->leads()->create([
                'nome' => $nome !== '' ? $nome : null,
                'telefone' => $telefoneOriginal,
                'email' => $email !== '' ? $email : null,
                'interesse' => $interesse !== '' ? $interesse : null,
                'classificacao' => in_array($classificacao, $classificacoesValidas, true) ? $classificacao : null,
                'status' => 'novo',
                'origem' => 'csv',
                'capturado_em' => now(),
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
