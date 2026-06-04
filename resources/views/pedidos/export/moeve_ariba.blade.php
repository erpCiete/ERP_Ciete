<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>ARIBA {{ $summary['numero_pedido'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 24px;
            color: #111827;
        }
        h1, h2, p {
            margin: 0;
        }
        .sheet {
            max-width: 980px;
        }
        .title {
            margin-bottom: 18px;
            font-size: 20px;
            font-weight: 700;
        }
        .top-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 18px;
        }
        .field {
            border: 1px solid #111827;
            min-height: 70px;
            padding: 8px 10px;
        }
        .label {
            display: block;
            margin-bottom: 6px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .value {
            font-size: 13px;
            font-weight: 600;
            line-height: 1.35;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #111827;
            padding: 8px 10px;
            font-size: 12px;
            vertical-align: top;
        }
        th {
            background: #f3f4f6;
            text-align: left;
        }
        .num {
            text-align: right;
            white-space: nowrap;
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
        .meta {
            margin-bottom: 12px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    @php
        $money = static fn ($value) => number_format((float) $value, 2, ',', '.') . ' €';
        $units = static fn ($value) => number_format((float) $value, 2, ',', '.');
    @endphp
    <main class="sheet">
        <h1 class="title">ARIBA - TRAMITACION DE PEDIDOS</h1>
        <p class="meta">Pedido {{ $summary['numero_pedido'] }} · Trabajo {{ $summary['trabajo_numero'] }}</p>

        <section class="top-grid">
            <div class="field">
                <span class="label">Propuesta de Inversión - \Opex acción gasto</span>
                <div class="value">{{ $summary['propuesta_opex'] }}</div>
            </div>
            <div class="field">
                <span class="label">Acción de gasto AC</span>
                <div class="value">{{ $summary['accion_gasto'] }}</div>
            </div>
            <div class="field">
                <span class="label">Sociedad</span>
                <div class="value">{{ $summary['sociedad_ariba'] }}</div>
            </div>
            <div class="field">
                <span class="label">Cta. de Mayor</span>
                <div class="value">{{ $summary['cta_mayor'] }}</div>
            </div>
        </section>

        <table>
            <thead>
                <tr>
                    <th style="width: 18%;">ID de producto/ ID del contrato</th>
                    <th style="width: 14%;">Producto</th>
                    <th style="width: 10%;" class="num">Cantidad</th>
                    <th>Texto Proveedor</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td>{{ $row['id_producto_contrato'] ?: $summary['contrato_codigo'] ?: 'Pendiente de parametrizar' }}</td>
                        <td>{{ $row['producto'] }}</td>
                        <td class="num">{{ $units($row['cantidad']) }}</td>
                        <td>{{ $row['texto_proveedor'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table style="margin-top: 14px;">
            <tbody>
                <tr>
                    <th style="width: 28%;">Descripción</th>
                    <td>{{ $summary['descripcion'] }}</td>
                </tr>
                <tr>
                    <th>Centro /Concesión</th>
                    <td>{{ $summary['nombre_estacion'] }}</td>
                </tr>
                <tr>
                    <th>Precio</th>
                    <td>{{ $money($summary['importe_pedido']) }}</td>
                </tr>
                <tr>
                    <th>Proveedor/ Contrato</th>
                    <td>{{ $summary['proveedor_contrato'] }}</td>
                </tr>
                <tr>
                    <th>Confirmar</th>
                    <td>{{ $summary['confirmar'] }}</td>
                </tr>
            </tbody>
        </table>

        @if ($summary['has_pending_fields'])
            <div class="pending">
                <strong>Campos pendientes de parametrizar</strong>
                {{ implode(' · ', $summary['pending_fields']) }}
            </div>
        @endif
    </main>
</body>
</html>
