<?php

namespace App\Http\Controllers\Web;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Reescrito en Fase 18 para el negocio mayorista: reemplaza "En Servicio"
 * (reparaciones activas) y "Stock Disponible" (InventoryItem, celulares)
 * por Pedidos Pendientes (Fase 17) y Stock de catálogo (Fase 6), mismo
 * criterio que Reportes (Fase 16). De paso se reemplazaron dos badges que
 * eran literales hardcodeados ("+12.5% vs mes ant.", "ROI: 42%") -- nunca
 * habian salido de datos reales -- por el crecimiento y margen calculados
 * de verdad.
 */
class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->user()->role === \App\Enums\UserRole::SUPER_ADMIN) {
            return redirect()->route('super-admin.dashboard');
        }

        $orgId = auth()->user()->organization_id;

        $salesThisMonth = Sale::where('organization_id', $orgId)
            ->whereMonth('sold_at', now()->month)
            ->whereYear('sold_at', now()->year)
            ->sum('total');

        $lastMonth = now()->subMonthNoOverflow();
        $salesLastMonth = Sale::where('organization_id', $orgId)
            ->whereMonth('sold_at', $lastMonth->month)
            ->whereYear('sold_at', $lastMonth->year)
            ->sum('total');

        $salesGrowthPercent = $salesLastMonth > 0
            ? round((($salesThisMonth - $salesLastMonth) / $salesLastMonth) * 100, 1)
            : null; // sin mes anterior para comparar, no inventamos un numero

        $profitMonth = Sale::where('organization_id', $orgId)
            ->whereMonth('sold_at', now()->month)
            ->whereYear('sold_at', now()->year)
            ->sum('profit_total');

        $marginPercent = $salesThisMonth > 0 ? round(($profitMonth / $salesThisMonth) * 100, 1) : 0;

        $pendingOrders = Order::where('organization_id', $orgId)
            ->whereIn('status', [OrderStatus::DRAFT, OrderStatus::CONFIRMED])
            ->count();

        // Stock de catalogo (Fase 6), no InventoryItem (celulares, Fase 1).
        $stockRows = DB::table('product_stocks')
            ->join('products', 'products.id', '=', 'product_stocks.product_id')
            ->where('products.organization_id', $orgId)
            ->select('product_stocks.quantity', 'products.retail_price', 'products.wholesale_price')
            ->get();

        $metrics = [
            'sales_month' => $salesThisMonth,
            'sales_growth_percent' => $salesGrowthPercent,
            'profit_month' => $profitMonth,
            'margin_percent' => $marginPercent,
            'pending_orders' => $pendingOrders,
            'stock_count' => $stockRows->where('quantity', '>', 0)->count(),
            'stock_value' => $stockRows->sum(fn ($r) => $r->quantity * (float) ($r->retail_price ?? $r->wholesale_price ?? 0)),
        ];

        $recent_sales = Sale::where('organization_id', $orgId)
            ->with(['client'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recent_orders = Order::where('organization_id', $orgId)
            ->with(['client'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Fase "pro" del dashboard: alerta de stock bajo visible sin esperar
        // a la notificacion (Fase 20 solo avisa en el momento en que cruza el
        // umbral; esto muestra el estado actual completo). Mismo criterio que
        // el filtro low_stock de StockController@index.
        $lowStockProducts = Product::with('stock')
            ->where(function ($q) {
                $q->whereHas('stock', fn ($s) => $s->whereColumn('quantity', '<=', 'products.min_stock'))
                    ->orWhereDoesntHave('stock');
            })
            ->where('min_stock', '>', 0)
            ->get()
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'current_stock' => $p->stock->quantity ?? 0,
                'min_stock' => $p->min_stock,
            ])
            // mas critico primero: el que esta mas lejos por debajo de su minimo
            ->sortBy(fn ($p) => $p['current_stock'] - $p['min_stock'])
            ->take(6)
            ->values();

        // Productos mas vendidos del mes (unidades), para ver de un vistazo
        // que esta funcionando sin entrar a Reportes.
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.organization_id', $orgId)
            ->whereMonth('sales.sold_at', now()->month)
            ->whereYear('sales.sold_at', now()->year)
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(sale_items.quantity) as units_sold'),
                DB::raw('SUM(sale_items.line_total) as revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('units_sold')
            ->limit(5)
            ->get();

        // Mini-tendencia de ventas de los ultimos 7 dias, para el sparkline
        // del dashboard.
        $salesByDay = Sale::where('organization_id', $orgId)
            ->where('sold_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(sold_at) as day, SUM(total) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $salesTrend = collect(range(6, 0))->map(function (int $daysAgo) use ($salesByDay) {
            $day = now()->subDays($daysAgo);
            $key = $day->format('Y-m-d');

            return [
                'label' => $day->locale('es')->isoFormat('ddd'),
                'total' => (float) ($salesByDay[$key] ?? 0),
            ];
        })->values();

        return view('dashboard', compact('metrics', 'recent_sales', 'recent_orders', 'lowStockProducts', 'topProducts', 'salesTrend'));
    }
}
