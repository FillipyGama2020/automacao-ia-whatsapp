<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\Element\AbstractContainer;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser as PdfParser;
use Throwable;

class ExtrairTextoDocumentoService
{
    private const TAMANHO_MAXIMO = 12000;

    public function extrair(string $caminhoRelativo, string $extensao): ?string
    {
        $caminhoAbsoluto = Storage::disk('public')->path($caminhoRelativo);

        try {
            $texto = match (strtolower($extensao)) {
                'pdf' => $this->extrairDePdf($caminhoAbsoluto),
                'docx' => $this->extrairDeDocx($caminhoAbsoluto),
                'txt' => $this->extrairDeTxt($caminhoAbsoluto),
                default => null,
            };
        } catch (Throwable $e) {
            Log::warning('Falha ao extrair texto de documento', ['caminho' => $caminhoRelativo, 'erro' => $e->getMessage()]);

            return null;
        }

        if (! $texto) {
            return null;
        }

        $texto = trim(preg_replace('/[ \t]+/', ' ', preg_replace('/\n{3,}/', "\n\n", $texto)));

        if ($texto === '') {
            return null;
        }

        if (mb_strlen($texto) > self::TAMANHO_MAXIMO) {
            $texto = mb_substr($texto, 0, self::TAMANHO_MAXIMO).'... (documento truncado, texto muito longo)';
        }

        return $texto;
    }

    private function extrairDePdf(string $caminho): ?string
    {
        $pdf = (new PdfParser)->parseFile($caminho);

        return $pdf->getText() ?: null;
    }

    private function extrairDeDocx(string $caminho): ?string
    {
        $documento = IOFactory::load($caminho, 'Word2007');
        $partes = [];

        foreach ($documento->getSections() as $secao) {
            $this->coletarTextoDeContainer($secao, $partes);
        }

        return $partes ? implode("\n", $partes) : null;
    }

    private function coletarTextoDeContainer(AbstractContainer $container, array &$partes): void
    {
        foreach ($container->getElements() as $elemento) {
            if ($elemento instanceof Text) {
                $partes[] = $elemento->getText();
            } elseif ($elemento instanceof TextRun) {
                $linha = [];
                foreach ($elemento->getElements() as $sub) {
                    if ($sub instanceof Text) {
                        $linha[] = $sub->getText();
                    }
                }
                if ($linha) {
                    $partes[] = implode('', $linha);
                }
            } elseif ($elemento instanceof AbstractContainer) {
                $this->coletarTextoDeContainer($elemento, $partes);
            }
        }
    }

    private function extrairDeTxt(string $caminho): ?string
    {
        $conteudo = file_get_contents($caminho);

        if ($conteudo === false) {
            return null;
        }

        if (! mb_check_encoding($conteudo, 'UTF-8')) {
            $conteudo = mb_convert_encoding($conteudo, 'UTF-8', 'ISO-8859-1');
        }

        return $conteudo;
    }
}
