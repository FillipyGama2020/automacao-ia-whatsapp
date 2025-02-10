<?php

namespace App\Services;

use App\Models\Agente;
use Illuminate\Support\Facades\Storage;

class AgenteConteudoService
{
    public function syncHorarios(Agente $agente, array $horarios): void
    {
        $agente->horarios()->delete();

        foreach (array_keys(Agente::diasSemana()) as $dia) {
            $linha = $horarios[$dia] ?? [];
            $fechado = ! empty($linha['fechado']);

            $agente->horarios()->create([
                'dia_semana' => $dia,
                'fechado' => $fechado,
                'hora_inicio' => $fechado ? null : ($linha['hora_inicio'] ?? null),
                'hora_fim' => $fechado ? null : ($linha['hora_fim'] ?? null),
            ]);
        }
    }

    public function syncFeriados(Agente $agente, array $feriados): void
    {
        $agente->feriados()->delete();

        foreach ($feriados as $feriado) {
            if (empty($feriado['data'])) {
                continue;
            }

            $agente->feriados()->create([
                'data' => $feriado['data'],
                'descricao' => $feriado['descricao'] ?? null,
            ]);
        }
    }

    public function syncRegras(Agente $agente, array $regras): void
    {
        $agente->regras()->delete();

        foreach ($regras as $regra) {
            if (trim((string) $regra) === '') {
                continue;
            }

            $agente->regras()->create(['regra' => $regra]);
        }
    }

    public function syncFaqs(Agente $agente, array $faqs): void
    {
        $agente->faqs()->delete();

        foreach ($faqs as $faq) {
            if (empty($faq['pergunta']) || empty($faq['resposta'])) {
                continue;
            }

            $agente->faqs()->create([
                'pergunta' => $faq['pergunta'],
                'resposta' => $faq['resposta'],
            ]);
        }
    }

    public function syncProdutos(Agente $agente, array $produtos, array $arquivos): void
    {
        $mantidos = [];

        foreach ($produtos as $index => $item) {
            if (empty($item['nome'])) {
                continue;
            }

            $dados = [
                'tipo' => $item['tipo'] ?? 'produto',
                'nome' => $item['nome'],
                'preco' => ($item['preco'] ?? '') !== '' ? $item['preco'] : null,
                'descricao' => $item['descricao'] ?? null,
                'categoria' => $item['categoria'] ?? null,
            ];

            $produtoExistente = ! empty($item['id']) ? $agente->produtos()->find($item['id']) : null;

            $arquivo = $arquivos[$index]['imagem'] ?? null;
            if ($arquivo) {
                if ($produtoExistente?->imagem) {
                    Storage::disk('public')->delete($produtoExistente->imagem);
                }

                $dados['imagem'] = $arquivo->store('agentes/produtos', 'public');
            }

            $produto = $produtoExistente
                ? tap($produtoExistente)->update($dados)
                : $agente->produtos()->create($dados);

            $mantidos[] = $produto->id;
        }

        $removidos = $agente->produtos()->whereNotIn('id', $mantidos)->get();

        foreach ($removidos as $produto) {
            if ($produto->imagem) {
                Storage::disk('public')->delete($produto->imagem);
            }

            $produto->delete();
        }
    }

    public function syncPoliticas(Agente $agente, array $politicas): void
    {
        $agente->politicas()->delete();

        foreach ($politicas as $politica) {
            if (empty($politica['titulo']) || empty($politica['conteudo'])) {
                continue;
            }

            $agente->politicas()->create([
                'titulo' => $politica['titulo'],
                'conteudo' => $politica['conteudo'],
            ]);
        }
    }

    public function syncDocumentos(Agente $agente, array $documentos, array $arquivos): void
    {
        $mantidos = [];

        foreach ($documentos as $index => $item) {
            if (empty($item['nome'])) {
                continue;
            }

            $tipo = $item['tipo'] ?? 'arquivo';

            $dados = [
                'tipo' => $tipo,
                'nome' => $item['nome'],
                'url' => $tipo === 'link' ? ($item['url'] ?? null) : null,
                'descricao' => $item['descricao'] ?? null,
            ];

            $documentoExistente = ! empty($item['id']) ? $agente->documentos()->find($item['id']) : null;

            $arquivo = $tipo === 'arquivo' ? ($arquivos[$index]['arquivo'] ?? null) : null;
            if ($arquivo) {
                if ($documentoExistente?->arquivo) {
                    Storage::disk('public')->delete($documentoExistente->arquivo);
                }

                $dados['arquivo'] = $arquivo->store('agentes/documentos', 'public');
            } elseif ($tipo === 'link' && $documentoExistente?->arquivo) {
                Storage::disk('public')->delete($documentoExistente->arquivo);
                $dados['arquivo'] = null;
            }

            $documento = $documentoExistente
                ? tap($documentoExistente)->update($dados)
                : $agente->documentos()->create($dados);

            $mantidos[] = $documento->id;
        }

        $removidos = $agente->documentos()->whereNotIn('id', $mantidos)->get();

        foreach ($removidos as $documento) {
            if ($documento->arquivo) {
                Storage::disk('public')->delete($documento->arquivo);
            }

            $documento->delete();
        }
    }
}
