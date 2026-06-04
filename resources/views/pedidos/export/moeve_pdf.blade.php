<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Pedido Moeve {{ $summary['numero_pedido'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #111827;
            margin: 22px;
        }
        h1, h2, p {
            margin: 0;
        }
        .sheet {
            border: 1px solid #111827;
            padding: 18px 18px 22px;
        }
        .topline {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 14px;
            font-size: 12px;
        }
        .offer-title {
            margin: 10px 0 18px;
            text-align: center;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.04em;
        }
        .header-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }
        .header-cell {
            border: 1px solid #111827;
            padding: 8px 10px;
            min-height: 58px;
        }
        .header-label {
            display: block;
            margin-bottom: 5px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .header-value {
            font-size: 13px;
            font-weight: 600;
            line-height: 1.35;
        }
        .work-title {
            margin-bottom: 14px;
            padding: 10px 12px;
            border: 1px solid #111827;
            font-size: 16px;
            font-weight: 700;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #111827;
            padding: 7px 8px;
            font-size: 12px;
            vertical-align: top;
        }
        th {
            text-align: left;
            background: #f3f4f6;
        }
        .num {
            text-align: right;
            white-space: nowrap;
        }
        .totals {
            width: 100%;
            margin-top: 12px;
            border-collapse: collapse;
        }
        .totals td {
            border: 1px solid #111827;
            padding: 8px;
            font-size: 12px;
        }
        .totals .label {
            width: 82%;
            font-weight: 700;
            text-align: right;
        }
        .totals .value {
            font-weight: 700;
            text-align: right;
            white-space: nowrap;
        }
        .note {
            margin-top: 14px;
            font-size: 12px;
            line-height: 1.45;
        }
        .approval {
            margin-top: 14px;
            font-size: 12px;
        }
        .pending {
            margin-top: 14px;
            border: 1px solid #111827;
            padding: 10px 12px;
            font-size: 11px;
        }
        .pending strong {
            display: block;
            margin-bottom: 4px;
        }
        @media print {
            body {
                margin: 10mm;
            }
        }
    </style>
</head>
<body>
    @php
        $money = static fn ($value) => number_format((float) $value, 2, ',', '.') . ' €';
        $units = static fn ($value) => number_format((float) $value, 2, ',', '.');
    @endphp
    <main class="sheet">
        <div class="topline">
            <p>Pedido Moeve {{ $summary['numero_pedido'] }}</p>
            <p>Trabajo {{ $summary['trabajo_numero'] }}</p>
        </div>

        <h1 class="offer-title">{{ $summary['offer_title'] }}</h1>

        <section class="header-grid">
            <div class="header-cell">
                <span class="header-label">E.S. Nº</span>
                <div class="header-value">{{ $summary['es_numero'] }}</div>
            </div>
            <div class="header-cell">
                <span class="header-label">Fecha</span>
                <div class="header-value">{{ $summary['fecha'] ?: 'Pendiente de parametrizar' }}</div>
            </div>
            <div class="header-cell">
                <span class="header-label">Nombre</span>
                <div class="header-value">{{ $summary['nombre_estacion'] }}</div>
            </div>
            <div class="header-cell">
                <span class="header-label">Localidad</span>
                <div class="header-value">{{ $summary['localidad'] }}</div>
            </div>
            <div class="header-cell">
                <span class="header-label">Trabajo encargado por</span>
                <div class="header-value">{{ $summary['trabajo_encargado_por'] }}</div>
            </div>
        </section>

        <div class="work-title">{{ $summary['trabajo_descripcion'] }}</div>

        <table>
            <thead>
                <tr>
                    <th style="width: 7%;">Item</th>
                    <th>Descripción</th>
                    <th style="width: 16%;">Código MOEVE</th>
                    <th style="width: 10%;" class="num">Unidades</th>
                    <th style="width: 14%;" class="num">Precio unidad</th>
                    <th style="width: 14%;" class="num">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td>{{ $row['item'] }}</td>
                        <td>
                            {{ $row['texto_proveedor'] }}
                            @if (! empty($row['detalle']))
                                <div style="margin-top: 4px; font-size: 11px;">{{ $row['detalle'] }}</div>
                            @endif
                        </td>
                        <td>{{ $row['codigo_moeve'] }}</td>
                        <td class="num">{{ $units($row['cantidad']) }}</td>
                        <td class="num">{{ $money($row['precio_unitario']) }}</td>
                        <td class="num">{{ $money($row['total_linea']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td class="label">Total</td>
                <td class="value">{{ $money($summary['importe_pedido']) }}</td>
            </tr>
        </table>

        <p class="note">{{ $summary['nota'] }}</p>
        <p class="approval"><strong>Aprobado por:</strong> {{ $summary['aprobado_por'] }}</p>

        @if ($summary['has_pending_fields'])
            <div class="pending">
                <strong>Campos pendientes de parametrizar</strong>
                {{ implode(' · ', $summary['pending_fields']) }}
            </div>
        @endif
    </main>
</body>
</html>
