<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DownloadMidiaService
{
    private const TAMANHO_MAXIMO = 16 * 1024 * 1024;

    private const EXTENSOES_POR_TIPO = [
        'imagem' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'audio' => ['ogg', 'mp3', 'm4a', 'amr', 'opus'],
        'video' => ['mp4', '3gp'],
        'documento' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'],
    ];

    public function baixarEArmazenar(string $url, string $tipo, int $conversaId, ?string $accessToken = null): ?string
    {
        if (! in_array($tipo, array_keys(self::EXTENSOES_POR_TIPO), true)) {
            return null;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL) || ! str_starts_with($url, 'https://')) {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $dominiosPermitidos = array_map('strtolower', config('services.meta.dominios_midia_permitidos', []));

        if (! in_array($host, $dominiosPermitidos, true)) {
            Log::warning('Download de anexo bloqueado: domínio não permitido', ['url' => $url, 'host' => $host]);

            return null;
        }

        try {
            $response = Http::withToken($accessToken)
                ->timeout(15)
                ->get($url);
        } catch (\Throwable $e) {
            Log::warning('Falha ao baixar anexo de mensagem', ['url' => $url, 'erro' => $e->getMessage()]);

            return null;
        }

        if (! $response->successful()) {
            Log::warning('Download de anexo retornou erro', ['url' => $url, 'status' => $response->status()]);

            return null;
        }

        $conteudo = $response->body();

        if (strlen($conteudo) === 0 || strlen($conteudo) > self::TAMANHO_MAXIMO) {
            Log::warning('Anexo rejeitado por tamanho', ['url' => $url, 'bytes' => strlen($conteudo)]);

            return null;
        }

        $extensao = $this->extensaoParaTipo($tipo, $response->header('Content-Type'));
        $nomeArquivo = Str::random(32).'.'.$extensao;
        $caminho = "conversas/{$conversaId}/{$nomeArquivo}";

        Storage::disk('public')->put($caminho, $conteudo);

        return $caminho;
    }

    private function extensaoParaTipo(string $tipo, ?string $contentType): string
    {
        $mapaContentType = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'audio/ogg' => 'ogg',
            'audio/mpeg' => 'mp3',
            'audio/mp4' => 'm4a',
            'audio/amr' => 'amr',
            'video/mp4' => 'mp4',
            'video/3gpp' => '3gp',
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'text/plain' => 'txt',
        ];

        $contentType = strtolower(trim(explode(';', $contentType ?? '')[0] ?? ''));

        return $mapaContentType[$contentType] ?? self::EXTENSOES_POR_TIPO[$tipo][0];
    }
}
