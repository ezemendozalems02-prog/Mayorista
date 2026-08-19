<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nueva Venta - Mito Yamile</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7f6; }
        .container { max-width: 600px; margin: 20px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: #6366f1; padding: 30px 20px; text-align: center; color: #fff; }
        .header h1 { margin: 0; font-size: 20px; text-transform: uppercase; letter-spacing: 2px; font-weight: 900; font-style: italic; }
        .content { padding: 30px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { text-align: left; font-size: 10px; font-weight: 900; color: #999; text-transform: uppercase; padding: 10px 0; border-bottom: 1px solid #f0f0f0; }
        .items-table td { padding: 15px 0; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
        .item-name { font-weight: 700; font-size: 14px; color: #333; }
        .totals { background: #f9fafb; padding: 25px; border-radius: 12px; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #999; background: #fff; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-label { font-size: 10px; font-weight: 900; color: #999; text-transform: uppercase; letter-spacing: 1px; }
        .info-value { font-size: 15px; font-weight: 700; color: #333; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Mito <span style="color: #cbd5e1;">Yamile</span></h1>
            <p style="margin: 8px 0 0; opacity: 0.8; font-weight: 600; font-size: 13px;">Notificación de Venta #{{ $sale->sale_number }}</p>
        </div>
        <div class="content">
            <table class="info-table">
                <tr>
                    <td>
                        <div class="info-label">Cliente</div>
                        <div class="info-value">{{ $sale->client->full_name ?? 'Cliente Genérico' }}</div>
                    </td>
                    <td style="text-align: right;">
                        <div class="info-label">Fecha y Hora</div>
                        <div class="info-value">{{ $sale->sold_at->format('d/m/Y H:i') }} hs</div>
                    </td>
                </tr>
            </table>

            <table class="items-table">
                <thead>
                    <tr>
                        <th>Producto / Servicio</th>
                        <th style="text-align: right;">Precio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $item)
                    <tr>
                        <td class="item-name">{{ $item->item_name }} (x{{ $item->quantity }})</td>
                        <td style="text-align: right; font-weight: 700; font-size: 14px;">{{ $sale->currency }} ${{ number_format($item->line_total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="totals">
                <table style="width: 100%;">
                    <tr>
                        <td style="font-size: 14px; font-weight: 600; color: #666;">Subtotal</td>
                        <td style="text-align: right; font-size: 14px; font-weight: 600; color: #666;">{{ $sale->currency }} ${{ number_format($sale->subtotal, 2) }}</td>
                    </tr>
                    @if($sale->discount > 0)
                    <tr>
                        <td style="font-size: 14px; font-weight: 600; color: #666; padding-top: 5px;">Descuento</td>
                        <td style="text-align: right; font-size: 14px; font-weight: 600; color: #ef4444; padding-top: 5px;">- {{ $sale->currency }} ${{ number_format($sale->discount, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="border-top: 20px solid transparent;"></td>
                        <td style="border-top: 20px solid transparent;"></td>
                    </tr>
                    <tr>
                        <td style="border-top: 2px solid #e5e7eb; padding-top: 15px; font-weight: 900; color: #333; font-size: 18px; text-transform: uppercase; font-style: italic;">Total de Venta</td>
                        <td style="border-top: 2px solid #e5e7eb; padding-top: 15px; text-align: right; font-weight: 900; color: #6366f1; font-size: 22px; font-style: italic;">
                            {{ $sale->currency }} ${{ number_format($sale->total, 2) }}
                        </td>
                    </tr>
                </table>
            </div>
            
            <p style="margin-top: 30px; font-size: 12px; color: #999; text-align: center;">
                Recibiste este mail porque el negocio <strong>{{ $sale->organization->name }}</strong> tiene activas las notificaciones automáticas de ventas.
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Mito Yamile • Gestión Mayorista de Juguetería, Librería y Regalería.
        </div>
    </div>
</body>
</html>
