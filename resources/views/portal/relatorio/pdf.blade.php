<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório — {{ $cliente->nome_empresa }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 18px; margin: 0 0 2px 0; }
        h2 { font-size: 11px; color: #6b7280; font-weight: normal; margin: 0 0 16px 0; }
        h3 { font-size: 13px; margin: 0 0 8px 0; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        .secao { margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 3px 6px; vertical-align: top; }
        .rotulo { color: #6b7280; }
        .valor { text-align: right; color: #1f2937; }
        .resumo-grid td { width: 33%; padding: 6px; }
        .resumo-label { font-size: 9px; text-transform: uppercase; color: #9ca3af; display: block; }
        .resumo-valor { font-size: 14px; font-weight: bold; }
        .barra-fundo { background: #f3f4f6; height: 10px; }
        .barra-cheia { background: #6366f1; height: 10px; }
        .rodape { margin-top: 24px; font-size: 9px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <h1>Relatório — {{ $cliente->nome_empresa }}</h1>
    <h2>Competência: {{ $competencia->format('m/Y') }} — gerado em {{ now()->format('d/m/Y H:i') }}</h2>

    <div class="secao">
        <h3>Atendimento</h3>
        <table class="resumo-grid">
            <tr>
                <td><span class="resumo-label">Conversas</span><span class="resumo-valor">{{ $relatorio['atendimento']['total'] }}</span></td>
                <td><span class="resumo-label">Avaliação média</span><span class="resumo-valor">{{ $relatorio['atendimento']['avaliacao_media'] !== null ? number_format($relatorio['atendimento']['avaliacao_media'], 1, ',', '.').' / 5' : '—' }}</span></td>
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

    <div class="rodape">Relatório gerado automaticamente pelo painel — {{ config('app.name') }}</div>
</body>
</html>
