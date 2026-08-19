<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Ticket #{{ $sale->sale_number }}</title>
    <style>
        @page {
            margin: 10mm 12mm;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
        }

        table { border-collapse: collapse; width: 100%; }
        .text-right  { text-align: right; }
        .text-center { text-align: center; }
        .bold        { font-weight: bold; }
        .gray        { color: #666; }
        .text-sm     { font-size: 8pt; }
        .text-xs     { font-size: 7pt; }

        /* ── Header ── */
        .header {
            border: 1.5pt solid #1a1a1a;
            padding: 10pt;
            text-align: center;
            margin-bottom: 8pt;
        }
        .store-name {
            font-size: 16pt;
            font-weight: bold;
            letter-spacing: 1pt;
        }
        .store-sub {
            font-size: 8pt;
            color: #555;
            margin-top: 3pt;
        }
        .ticket-title {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2pt;
            margin-top: 6pt;
            border-top: 0.5pt solid #ccc;
            padding-top: 6pt;
        }

        /* ── Section divider ── */
        .divider {
            border-top: 0.5pt solid #ccc;
            margin: 6pt 0;
        }
        .divider-thick {
            border-top: 1.5pt solid #1a1a1a;
            margin: 6pt 0;
        }

        /* ── Info rows ── */
        .info-row {
            margin-bottom: 3pt;
        }
        .info-label {
            font-size: 7pt;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
        }
        .info-value {
            font-size: 9pt;
            font-weight: bold;
        }

        /* ── Items table ── */
        .items-table thead th {
            background-color: #1a1a1a;
            color: #fff;
            padding: 4pt 5pt;
            font-size: 7.5pt;
            text-align: left;
        }
        .items-table thead th.text-right { text-align: right; }
        .items-table tbody td {
            padding: 4pt 5pt;
            border-bottom: 0.3pt solid #e0e0e0;
            vertical-align: top;
            font-size: 8.5pt;
        }
        .items-table tbody tr:nth-child(even) td {
            background-color: #f9f9f9;
        }
        .item-name { font-weight: bold; }
        .item-imei { font-size: 7pt; color: #888; }

        /* ── Totals ── */
        .totals-table { width: 55%; margin-left: auto; margin-top: 6pt; }
        .totals-table td { padding: 2pt 5pt; font-size: 9pt; }
        .total-row td {
            border-top: 1.5pt solid #1a1a1a;
            font-size: 12pt;
            font-weight: bold;
            padding-top: 5pt;
        }

        /* ── Payments ── */
        .payments-table td { padding: 3pt 5pt; font-size: 8.5pt; }

        /* ── Footer ── */
        .footer {
            text-align: center;
            margin-top: 10pt;
            padding-top: 8pt;
            border-top: 0.5pt solid #ccc;
            font-size: 7pt;
            color: #999;
        }
    </style>
</head>
<body>

{{-- ═══════════ HEADER ═══════════ --}}
<div class="header">
    <div class="store-name">{{ $organization->name }}</div>
    @if($fiscalSetting)
        <div class="store-sub">
            CUIT: {{ $fiscalSetting->cuit }} — {{ str_replace('_', ' ', $fiscalSetting->condicion_iva) }}
        </div>
    @endif
    @if($organization->address ?? false)
        <div class="store-sub">{{ $organization->address }}</div>
    @endif
    <div class="ticket-title">Comprobante de Venta</div>
</div>

{{-- ═══════════ SALE INFO ═══════════ --}}
<table>
    <tr>
        <td style="width: 50%;">
            <div class="info-row">
                <div class="info-label">N° Comprobante</div>
                <div class="info-value">#{{ $sale->sale_number }}</div>
            </div>
        </td>
        <td style="width: 50%; text-align: right;">
            <div class="info-row">
                <div class="info-label">Fecha</div>
                <div class="info-value">{{ ($sale->sold_at ?? $sale->created_at)->format('d/m/Y H:i') }}</div>
            </div>
        </td>
    </tr>
</table>

@if($sale->seller)
<div class="info-row" style="margin-top: 4pt;">
    <span class="info-label">Vendedor:</span>
    <span class="info-value">{{ $sale->seller->name }}</span>
</div>
@endif

{{-- ═══════════ CLIENT ═══════════ --}}
@if($sale->client)
<div class="divider"></div>
<div class="info-label" style="margin-bottom: 3pt;">Cliente</div>
<table>
    <tr>
        <td style="width: 60%;">
            <div class="info-value">{{ $sale->client->full_name }}</div>
            @if($sale->client->document_number)
                <div class="text-xs gray">DNI: {{ $sale->client->document_number }}</div>
            @endif
        </td>
        <td style="width: 40%; text-align: right;">
            @if($sale->client->phone)
                <div class="text-xs gray">{{ $sale->client->phone }}</div>
            @endif
            @if($sale->client->email)
                <div class="text-xs gray">{{ $sale->client->email }}</div>
            @endif
        </td>
    </tr>
</table>
@endif

{{-- ═══════════ ITEMS ═══════════ --}}
<div class="divider-thick"></div>
<table class="items-table">
    <thead>
        <tr>
            <th style="width: 52%;">Descripción</th>
            <th style="width: 8%; text-align: center;">Cant.</th>
            <th style="width: 20%; text-align: right;">Unitario</th>
            <th style="width: 20%; text-align: right;">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sale->items as $item)
        <tr>
            <td>
                <div class="item-name">{{ $item->item_name }}</div>
                @if($item->inventoryItem?->imei)
                    <div class="item-imei">IMEI: {{ $item->inventoryItem->imei }}</div>
                @endif
            </td>
            <td style="text-align: center;">{{ $item->quantity }}</td>
            <td class="text-right">{{ $sale->currency }} {{ number_format($item->unit_price, 2, ',', '.') }}</td>
            <td class="text-right bold">{{ $sale->currency }} {{ number_format($item->line_total, 2, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- ═══════════ TOTALS ═══════════ --}}
<table class="totals-table">
    <tr>
        <td class="gray">Subtotal:</td>
        <td class="text-right">{{ $sale->currency }} {{ number_format($sale->subtotal, 2, ',', '.') }}</td>
    </tr>
    @if($sale->discount > 0)
    <tr>
        <td class="gray">Descuento:</td>
        <td class="text-right" style="color: #e53e3e;">- {{ $sale->currency }} {{ number_format($sale->discount, 2, ',', '.') }}</td>
    </tr>
    @endif
    <tr class="total-row">
        <td>TOTAL:</td>
        <td class="text-right">{{ $sale->currency }} {{ number_format($sale->total, 2, ',', '.') }}</td>
    </tr>
</table>

{{-- ═══════════ PAYMENTS ═══════════ --}}
@if($sale->payments->isNotEmpty())
<div class="divider" style="margin-top: 8pt;"></div>
<div class="info-label" style="margin-bottom: 3pt;">Medios de Pago</div>
<table class="payments-table">
    @foreach($sale->payments as $payment)
    <tr>
        <td>{{ $payment->method }}</td>
        <td class="text-right bold">{{ $payment->currency }} {{ number_format($payment->amount, 2, ',', '.') }}</td>
    </tr>
    @endforeach
</table>
@endif

{{-- ═══════════ FOOTER ═══════════ --}}
<div class="footer">
    <div>¡Gracias por su compra!</div>
    <div style="margin-top: 3pt;">Documento no válido como comprobante fiscal</div>
    <div style="margin-top: 3pt;">Generado el {{ now()->format('d/m/Y H:i') }}</div>
</div>

</body>
</html>
