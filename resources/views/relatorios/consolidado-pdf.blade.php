<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório Consolidado</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 18px; margin: 0 0 2px 0; }
        h2 { font-size: 11px; color: #6b7280; font-weight: normal; margin: 0 0 16px 0; }
        h3 { font-size: 13px; margin: 0 0 8px 0; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        .secao { margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 3px 6px; vertical-align: top; }
        .rotulo { color: #6b7280; }
        .valor { text-align: right; color: #1f2937; }
        .resumo-grid td { width: 25%; padding: 6px; }
        .resumo-label { font-size: 9px; text-transform: uppercase; color: #9ca3af; display: block; }
        .resumo-valor { font-size: 14px; font-weight: bold; }
        .verde { color: #15803d; }
        .vermelho { color: #b91c1c; }
        .aviso { background: #fef3c7; border: 1px solid #fcd34d; padding: 8px; font-size: 10px; color: #92400e; }
        .barra-fundo { background: #f3f4f6; height: 10px; }
        .barra-cheia { background: #6366f1; height: 10px; }
        .tabela-clientes th { text-align: left; font-size: 9px; text-transform: uppercase; color: #9ca3af; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        .tabela-clientes td { border-bottom: 1px solid #f3f4f6; padding: 5px 6px; }
        .rodape { margin-top: 24px; font-size: 9px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <h1>Relatório Consolidado</h1>
    <h2>Competência: {{ $competencia->format('m/Y') }} — todos os clientes — gerado em {{ now()->format('d/m/Y H:i') }}</h2>

    <div class="secao">
        <h3>Atendimento</h3>
        <table class="resumo-grid">
            <tr>
                <td><span class="resumo-label">Conversas</span><span class="resumo-valor">{{ $relatorio['atendimento']['total'] }}</span></td>
                <td><span class="resumo-label">Avaliação média</span><span class="resumo-valor">{{ $relatorio['atendimento']['avaliacao_media'] !== null ? number_format($relatorio['atendimento']['avaliacao_media'], 1, ',', '.').' / 5' : '—' }}</span></td>
                <td><span class="resumo-label">Custo de IA</span><span class="resumo-valor">R$ {{ number_format($relatorio['custo_ia'], 4, ',', '.') }}</span></td>
            </tr>
        </table>
        <table>
            @foreach ($relatorio['atendimento']['por_status'] as $label => $total)
                <tr>
                    <td class="rotulo">{{ $label }}</td>
                    <td class="valor">{{ $total }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="secao">
        <h3>Mensagens ({{ $relatorio['mensagens']['total'] }} no período)</h3>
        <table>
            <tr>
                <td style="width: 50%">
                    <table>
                        @foreach ($relatorio['mensagens']['por_tipo'] as $label => $total)
                            @if ($total > 0)
                                <tr><td class="rotulo">{{ $label }}</td><td class="valor">{{ $total }}</td></tr>
                            @endif
                        @endforeach
                    </table>
                </td>
                <td style="width: 50%">
                    <table>
                        @foreach ($relatorio['mensagens']['por_remetente'] as $label => $total)
                            @if ($total > 0)
                                <tr><td class="rotulo">{{ $label }}</td><td class="valor">{{ $total }}</td></tr>
                            @endif
                        @endforeach
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="secao">
        <h3>Leads capturados ({{ $relatorio['leads']['total'] }} no período)</h3>
        <table>
            <tr>
                <td style="width: 50%">
                    <table>
                        @foreach ($relatorio['leads']['por_classificacao'] as $label => $total)
                            <tr><td class="rotulo">{{ $label }}</td><td class="valor">{{ $total }}</td></tr>
                        @endforeach
                    </table>
                </td>
                <td style="width: 50%">
                    <table>
                        @foreach ($relatorio['leads']['por_status'] as $label => $total)
                            <tr><td class="rotulo">{{ $label }}</td><td class="valor">{{ $total }}</td></tr>
                        @endforeach
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="secao">
        <h3>Mensagens por horário do dia</h3>
        @php $maiorHora = max($relatorio['horarios']) ?: 1; @endphp
        <table>
            @foreach ($relatorio['horarios'] as $hora => $total)
                @if ($total > 0)
                    <tr>
                        <td style="width: 30px">{{ str_pad($hora, 2, '0', STR_PAD_LEFT) }}h</td>
                        <td>
                            <table class="barra-fundo"><tr><td class="barra-cheia" style="width: {{ max(4, ($total / $maiorHora) * 100) }}%"></td></tr></table>
                        </td>
                        <td style="width: 24px" class="valor">{{ $total }}</td>
                    </tr>
                @endif
            @endforeach
        </table>
    </div>

    <div class="secao">
        <h3>Financeiro</h3>
        @if (! $relatorio['financeiro'])
            <div class="aviso">Nenhum cliente teve o mês fechado ainda — os dados financeiros não estão disponíveis para esta competência.</div>
        @else
            @php($fin = $relatorio['financeiro'])
            <table class="resumo-grid">
                <tr>
                    <td><span class="resumo-label">Receita total</span><span class="resumo-valor">R$ {{ number_format($fin['receita_total'], 2, ',', '.') }}</span></td>
                    <td><span class="resumo-label">Custo total</span><span class="resumo-valor">R$ {{ number_format($fin['custo_total'], 2, ',', '.') }}</span></td>
                    <td><span class="resumo-label">Lucro bruto</span><span class="resumo-valor {{ $fin['lucro_bruto'] >= 0 ? 'verde' : 'vermelho' }}">R$ {{ number_format($fin['lucro_bruto'], 2, ',', '.') }}</span></td>
                    <td><span class="resumo-label">Margem</span><span class="resumo-valor">{{ $fin['margem_percentual'] !== null ? number_format($fin['margem_percentual'], 1, ',', '.').'%' : '—' }}</span></td>
                </tr>
            </table>
        @endif
    </div>

    <div class="secao">
        <h3>Por cliente</h3>
        <table class="tabela-clientes">
            <tr>
                <th>Cliente</th>
                <th>Conversas</th>
                <th>Leads</th>
                <th>Custo IA</th>
                <th>Financeiro</th>
            </tr>
            @foreach ($relatorio['por_cliente'] as $linha)
                <tr>
                    <td>{{ $linha['cliente']->nome_empresa }}</td>
                    <td>{{ $linha['conversas'] }}</td>
                    <td>{{ $linha['leads'] }}</td>
                    <td>R$ {{ number_format($linha['custo_ia'], 4, ',', '.') }}</td>
                    <td>
                        @if ($linha['fechamento'])
                            R$ {{ number_format($linha['fechamento']->lucro_bruto, 2, ',', '.') }} lucro
                        @else
                            Mês não fechado
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="rodape">Relatório gerado automaticamente pelo painel — {{ config('app.name') }}</div>
</body>
</html>
