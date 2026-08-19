<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #171412;
            font-size: 12px;
            margin: 0;
        }

        .banner {
            background: #C81E3A;
            color: #ffffff;
            padding: 22px 36px;
        }
        .banner .marca {
            font-size: 11px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            opacity: 0.85;
            margin: 0 0 4px;
        }
        .banner h1 { font-size: 22px; margin: 0; }
        .banner .meta { font-size: 10px; opacity: 0.9; margin: 6px 0 0; }

        .cuerpo { padding: 24px 36px 36px; }

        .seccion-titulo {
            color: #C81E3A;
            font-size: 13px;
            font-weight: bold;
            margin: 0 0 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #FBEAEC;
        }
        .seccion { margin-bottom: 22px; }

        .resumen-item {
            display: inline-block;
            width: 31%;
            margin: 0 2% 10px 0;
            vertical-align: top;
            background: #FAF8F7;
            border: 1px solid #ECE9E7;
            border-radius: 6px;
            padding: 10px 12px;
        }
        .resumen-label { color: #8A8481; font-size: 8.5px; text-transform: uppercase; display: block; margin-bottom: 3px; }
        .resumen-valor { color: #171412; font-size: 15px; font-weight: bold; display: block; }

        .chart-wrap { width: 100%; }
        .chart-labels td { text-align: center; font-size: 7px; color: #8A8481; padding-top: 4px; }

        table { width: 100%; border-collapse: collapse; }
        thead th {
            background: #C81E3A;
            color: #ffffff;
            text-align: left;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 8px 10px;
        }
        thead th:first-child { border-radius: 4px 0 0 4px; }
        thead th:last-child { border-radius: 0 4px 4px 0; }
        tbody td { padding: 7px 10px; font-size: 10.5px; border-bottom: 1px solid #ECE9E7; }
        tbody tr:nth-child(even) { background: #FAF8F7; }
        td.num { text-align: right; }

        .footer {
            margin-top: 26px;
            padding-top: 10px;
            border-top: 1px solid #ECE9E7;
            font-size: 8.5px;
            color: #B7B2AF;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="banner">
        <p class="marca">Club de Fantasías · Panel de Administrador</p>
        <h1>{{ $titulo }}</h1>
        <p class="meta">
            @if ($periodoLabel) Periodo: {{ $periodoLabel }} &nbsp;·&nbsp; @endif
            Generado el {{ $generadoEn }}
        </p>
    </div>

    <div class="cuerpo">

        @if (!empty($resumen))
            <div class="seccion">
                <div class="seccion-titulo">Resumen</div>
                <div>
                    @foreach ($resumen as $r)
                        <div class="resumen-item">
                            <span class="resumen-label">{{ $r['label'] }}</span>
                            <span class="resumen-valor">{{ $r['valor'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if (!empty($chart))
            <div class="seccion">
                <div class="seccion-titulo">Tendencia</div>
                @php $maxValor = max(1, collect($chart['data'])->max()); @endphp
                <table class="chart-wrap" style="table-layout:fixed;">
                    <tr>
                        @foreach ($chart['data'] as $valor)
                            <td style="vertical-align:bottom; text-align:center; height:110px;">
                                <div style="background:{{ $valor > 0 ? '#C81E3A' : '#ECE9E7' }}; width:65%; margin:0 auto; height:{{ max(2, round(($valor / $maxValor) * 100)) }}px;"></div>
                            </td>
                        @endforeach
                    </tr>
                    <tr class="chart-labels">
                        @foreach ($chart['labels'] as $etiqueta)
                            <td>{{ $etiqueta }}</td>
                        @endforeach
                    </tr>
                </table>
            </div>
        @endif

        <div class="seccion">
            <div class="seccion-titulo">Detalle completo</div>
            <table>
                <thead>
                    <tr>
                        @foreach ($columnas as $i => $col)
                            <th style="{{ $i > 0 ? 'text-align:right' : '' }}">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($filas as $fila)
                        <tr>
                            @foreach ($fila as $i => $valor)
                                <td class="{{ $i > 0 ? 'num' : '' }}">{{ $valor }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($columnas) }}">Sin datos para este periodo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <p class="footer">Generado automáticamente desde el panel de administración — Club de Fantasías</p>
    </div>

</body>
</html>