<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Factura {{ $invoice->tipo_comprobante }} — {{ str_pad($invoice->punto_venta, 4, '0', STR_PAD_LEFT) }}-{{ str_pad($invoice->numero_comprobante, 8, '0', STR_PAD_LEFT) }}</title>
    <style>
        /* DomPDF uses DejaVu Sans for UTF-8 support (ñ, accents, peso sign) */
        @page {
            margin: 12mm 15mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
        }

        /* ── Layout helpers ── */
        table {
            border-collapse: collapse;
            width: 100%;
        }

        .w-full  { width: 100%; }
        .mb-4    { margin-bottom: 8pt; }
        .mb-2    { margin-bottom: 4pt; }
        .mt-4    { margin-top: 8pt; }
        .text-right  { text-align: right; }
        .text-center { text-align: center; }
        .text-sm { font-size: 8pt; }
        .text-xs { font-size: 7pt; }
        .bold    { font-weight: bold; }
        .gray    { color: #555; }

        /* ── Section divider ── */
        .section-divider {
            border-top: 1.5pt solid #1a1a1a;
            margin: 6pt 0;
        }
        .section-divider-light {
            border-top: 0.5pt solid #cccccc;
            margin: 6pt 0;
        }

        /* ── Header block ── */
        .header-table td {
            vertical-align: middle;
            padding: 4pt;
        }

        .cell-emisor {
            width: 42%;
            border-right: 1pt solid #1a1a1a;
            padding-right: 8pt;
        }

        .cell-tipo {
            width: 16%;
            text-align: center;
            border-right: 1pt solid #1a1a1a;
            border-left: 1pt solid #1a1a1a;
        }

        .cell-comprobante {
            width: 42%;
            padding-left: 8pt;
        }

        .tipo-letter {
            font-size: 54pt;
            font-weight: bold;
            line-height: 1;
            color: #1a1a1a;
            display: block;
        }

        .tipo-label {
            font-size: 7pt;
            color: #555;
            display: block;
            margin-top: 2pt;
        }

        .emisor-name {
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 3pt;
        }

        .header-border {
            border: 1.5pt solid #1a1a1a;
            padding: 6pt;
        }

        /* ── Comprobante info ── */
        .cbte-label {
            font-size: 7.5pt;
            color: #555;
        }

        .cbte-value {
            font-size: 10pt;
            font-weight: bold;
        }

        .cbte-row {
            margin-bottom: 4pt;
        }

        /* ── Client section ── */
        .section-title {
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #666;
            letter-spacing: 0.5pt;
            margin-bottom: 4pt;
        }

        .client-table td {
            padding: 2pt 4pt;
            vertical-align: top;
        }

        /* ── Items table ── */
        .items-table {
            margin-top: 6pt;
        }

        .items-table thead tr th {
            background-color: #1a1a1a;
            color: #ffffff;
            padding: 4pt 6pt;
            font-size: 8pt;
            font-weight: bold;
            text-align: left;
        }

        .items-table thead tr th.text-right {
            text-align: right;
        }

        .items-table tbody tr td {
            padding: 4pt 6pt;
            border-bottom: 0.5pt solid #e0e0e0;
            vertical-align: top;
        }

        .items-table tbody tr:nth-child(even) td {
            background-color: #f9f9f9;
        }

        .items-table tfoot tr td {
            padding: 3pt 6pt;
        }

        /* ── Totals ── */
        .totals-table {
            width: 50%;
            margin-left: auto;
            margin-top: 6pt;
        }

        .totals-table td {
            padding: 3pt 6pt;
        }

        .totals-table tr.total-row td {
            border-top: 1.5pt solid #1a1a1a;
            font-size: 11pt;
            font-weight: bold;
            padding-top: 5pt;
        }

        .totals-separator {
            border-top: 0.5pt solid #cccccc;
        }

        /* ── CAE footer ── */
        .cae-footer {
            border: 1.5pt solid #1a1a1a;
            padding: 8pt;
            margin-top: 12pt;
        }

        .cae-table td {
            vertical-align: middle;
            padding: 3pt 6pt;
        }

        .cae-cell-qr {
            width: 60pt;
            text-align: center;
        }

        .qr-placeholder {
            width: 54pt;
            height: 54pt;
            border: 1pt dashed #aaa;
            display: inline-block;
            text-align: center;
            vertical-align: middle;
            color: #aaa;
            font-size: 6.5pt;
            line-height: 54pt;
        }

        .cae-info-label {
            font-size: 7.5pt;
            color: #666;
        }

        .cae-info-value {
            font-size: 9.5pt;
            font-weight: bold;
            letter-spacing: 0.5pt;
        }

        .arca-badge {
            font-size: 8pt;
            font-weight: bold;
            color: #1a1a1a;
            margin-top: 4pt;
        }

        /* ── Page number ── */
        .page-footer {
            margin-top: 8pt;
            font-size: 7pt;
            color: #999;
            text-align: center;
        }
    </style>
</head>
<body>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- HEADER: Emisor | Tipo Comprobante | Número / Fecha         --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="header-border">
    <table class="header-table w-full">
        <tr>
            {{-- Emisor (left) --}}
            <td class="cell-emisor">
                <div class="emisor-name">
                    {{ $fiscalSetting?->razon_social ?? 'Razón Social no configurada' }}
                </div>

                @if($fiscalSetting)
                    <div class="mb-2">
                        <span class="gray text-sm">CUIT:</span>
                        <span class="bold">{{ $fiscalSetting->cuit }}</span>
                    </div>
                    <div class="mb-2">
                        <span class="gray text-sm">Condición frente al IVA:</span><br>
                        <span>{{ str_replace('_', ' ', $fiscalSetting->condicion_iva) }}</span>
                    </div>
                    <div>
                        <span class="gray text-sm">Punto de Venta:</span>
                        <span class="bold">{{ str_pad($fiscalSetting->punto_venta, 4, '0', STR_PAD_LEFT) }}</span>
                    </div>
                @endif
            </td>

            {{-- Tipo Comprobante (center — large letter, per AFIP layout) --}}
            <td class="cell-tipo">
                <span class="tipo-letter">{{ $invoice->tipo_comprobante }}</span>
                <span class="tipo-label">COD. {{ match($invoice->tipo_comprobante) {
                    'A' => '001',
                    'B' => '006',
                    'C' => '011',
                    default => '---',
                } }}</span>
            </td>

            {{-- Comprobante info (right) --}}
            <td class="cell-comprobante">
                <div class="text-center bold" style="font-size: 10pt; margin-bottom: 8pt;">
                    FACTURA ELECTRÓNICA
                </div>

                <div class="cbte-row">
                    <div class="cbte-label">Punto de Venta</div>
                    <div class="cbte-value">{{ str_pad($invoice->punto_venta, 4, '0', STR_PAD_LEFT) }}</div>
                </div>

                <div class="cbte-row">
                    <div class="cbte-label">Número de Comprobante</div>
                    <div class="cbte-value">{{ str_pad($invoice->numero_comprobante, 8, '0', STR_PAD_LEFT) }}</div>
                </div>

                <div class="cbte-row">
                    <div class="cbte-label">Fecha de Emisión</div>
                    <div class="cbte-value">
                        {{ $invoice->created_at ? $invoice->created_at->format('d/m/Y') : now()->format('d/m/Y') }}
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- CLIENT DATA                                                --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="mt-4">
    <div class="section-title">Datos del Receptor</div>
    <table class="client-table w-full" style="border: 0.5pt solid #ccc;">
        <tr>
            <td style="width: 50%; border-right: 0.5pt solid #ccc;">
                <span class="gray text-xs">Apellido y Nombre / Razón Social:</span><br>
                <span class="bold">{{ $invoice->cliente_nombre }}</span>
            </td>
            <td style="width: 25%; border-right: 0.5pt solid #ccc;">
                <span class="gray text-xs">CUIT / Documento:</span><br>
                @if($invoice->cliente_documento)
                    <span class="bold">{{ $invoice->cliente_documento }}</span>
                @else
                    <span class="gray">Consumidor Final</span>
                @endif
            </td>
            <td style="width: 25%;">
                <span class="gray text-xs">Condición frente al IVA:</span><br>
                @if($invoice->cliente_condicion_iva)
                    <span>{{ str_replace('_', ' ', $invoice->cliente_condicion_iva) }}</span>
                @elseif(!$invoice->cliente_documento)
                    <span class="gray">Consumidor Final</span>
                @else
                    <span class="gray">—</span>
                @endif
            </td>
        </tr>
    </table>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- ITEMS TABLE                                                --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="mt-4">
    <div class="section-title">Detalle de Productos / Servicios</div>
    <table class="items-table w-full">
        <thead>
            <tr>
                <th style="width: 52%;">Descripción</th>
                <th style="width: 12%; text-align: right;">Cantidad</th>
                <th style="width: 18%; text-align: right;">Precio Unitario</th>
                <th style="width: 18%; text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $item)
                <tr>
                    <td>{{ $item->descripcion }}</td>
                    <td class="text-right">{{ number_format($item->cantidad, 2, ',', '.') }}</td>
                    <td class="text-right">$ {{ number_format($item->precio_unitario, 2, ',', '.') }}</td>
                    <td class="text-right">$ {{ number_format($item->total, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center gray">Sin items registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- TOTALS                                                     --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<table class="totals-table">
    <tr>
        <td class="gray">Subtotal:</td>
        <td class="text-right">$ {{ number_format($invoice->subtotal, 2, ',', '.') }}</td>
    </tr>
    @if($invoice->tipo_comprobante !== 'C')
        <tr>
            <td class="gray">IVA (21%):</td>
            <td class="text-right">$ {{ number_format($invoice->iva, 2, ',', '.') }}</td>
        </tr>
    @endif
    <tr class="total-row">
        <td>TOTAL:</td>
        <td class="text-right">$ {{ number_format($invoice->total, 2, ',', '.') }}</td>
    </tr>
</table>

@if($invoice->tipo_comprobante === 'C')
    <div class="text-right text-xs gray mt-4" style="padding-right: 6pt;">
        * Factura C: IVA incluido en el total (no discriminado)
    </div>
@endif

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- CAE FOOTER                                                 --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="cae-footer">
    <table class="cae-table w-full">
        <tr>
            {{-- QR placeholder (Phase 5) --}}
            <td class="cae-cell-qr">
                @if($qrData)
                    {{-- Future: render QR image here --}}
                    <div class="qr-placeholder">QR</div>
                @else
                    <div class="qr-placeholder">QR<br>ARCA</div>
                @endif
            </td>

            {{-- CAE data --}}
            <td style="padding-left: 10pt;">
                <table>
                    <tr>
                        <td style="padding: 2pt 10pt 2pt 0;">
                            <div class="cae-info-label">CAE N°:</div>
                            <div class="cae-info-value">{{ $invoice->cae }}</div>
                        </td>
                        <td style="padding: 2pt 10pt;">
                            <div class="cae-info-label">Fecha de Vto. del CAE:</div>
                            <div class="cae-info-value">
                                {{ $invoice->cae_vencimiento ? $invoice->cae_vencimiento->format('d/m/Y') : '—' }}
                            </div>
                        </td>
                    </tr>
                </table>
                <div class="arca-badge" style="margin-top: 6pt;">
                    ✓ Comprobante Autorizado por ARCA
                </div>
            </td>

            {{-- Invoice number repeat (right side, per AFIP convention) --}}
            <td class="text-right" style="padding-left: 10pt; white-space: nowrap;">
                <div class="cae-info-label">Comprobante N°:</div>
                <div class="cae-info-value" style="font-size: 8.5pt;">
                    {{ str_pad($invoice->punto_venta, 4, '0', STR_PAD_LEFT) }}-{{ str_pad($invoice->numero_comprobante, 8, '0', STR_PAD_LEFT) }}
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- ── Page footer ── --}}
<div class="page-footer">
    Documento generado electrónicamente — No válido como tique fiscal
</div>

</body>
</html>
