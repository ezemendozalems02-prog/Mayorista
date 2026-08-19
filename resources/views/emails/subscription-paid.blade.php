<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nueva Suscripción - Mito Yamile</title>
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 0; background-color: #f1f5f9; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 24px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; }
        .header { background: #0f172a; padding: 60px 40px; text-align: center; color: #fff; }
        .header h1 { margin: 0; font-size: 28px; font-weight: 900; letter-spacing: -0.025em; font-style: italic; }
        .badge { display: inline-block; background: #6366f1; color: #fff; font-size: 11px; font-weight: 800; padding: 8px 16px; rounded: 100px; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 20px; border-radius: 50px;}
        .content { padding: 40px; }
        .stat-grid { display: grid; grid-template-cols: 1fr 1fr; gap: 20px; margin-bottom: 40px; }
        .stat-card { background: #f8fafc; padding: 20px; border-radius: 20px; border: 1px solid #f1f5f9; }
        .stat-label { font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
        .stat-value { font-size: 20px; font-weight: 800; color: #0f172a; letter-spacing: -0.025em; }
        .customer-box { background: #eef2ff; padding: 30px; border-radius: 24px; border: 1px solid #e0e7ff; margin-bottom: 40px; }
        .customer-name { font-size: 24px; font-weight: 900; color: #4338ca; margin-bottom: 8px; }
        .customer-detail { font-size: 14px; font-weight: 600; color: #6366f1; }
        .footer { padding: 40px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #f1f5f9; background: #f8fafc; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="badge">🚀 Nueva Conversión SaaS!</span>
            <h1>Mito <span style="color: #6366f1;">Yamile</span></h1>
            <p style="margin: 15px 0 0; opacity: 0.7; font-weight: 500; font-size: 16px;">¡Acabamos de registrar un nuevo pago de suscripción!</p>
        </div>
        <div class="content">
            <div class="customer-box">
                <div class="stat-label">Organización / Cliente</div>
                <div class="customer-name">{{ $organization->name }}</div>
                <div class="customer-detail">Slug: {{ $organization->slug }} • Email: {{ $organization->email }}</div>
            </div>

            <div style="display: flex; gap: 20px; margin-bottom: 40px;">
                <div class="stat-card" style="flex: 1;">
                    <div class="stat-label">Plan Adquirido</div>
                    <div class="stat-value" style="color: #6366f1;">{{ $plan->name }}</div>
                </div>
                <div class="stat-card" style="flex: 1;">
                    <div class="stat-label">Monto (USD/ARS)</div>
                    <div class="stat-value">$ {{ number_format($amount ?: $plan->effective_price, 2) }}</div>
                </div>
            </div>

            <div style="background: #fdf2f8; padding: 20px; border-radius: 20px; border: 1px solid #fce7f3; margin-bottom: 10px;">
                <p style="margin: 0; font-size: 14px; color: #be185d; font-weight: 700;">
                    🎉 ¡Felicidades! El negocio ya tiene activo su plan y los permisos han sido actualizados automáticamente en el sistema.
                </p>
            </div>
        </div>
        <div class="footer">
            Admin Dashboard • Mito Yamile &copy; {{ date('Y') }}
        </div>
    </div>
</body>
</html>
