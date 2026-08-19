<?php

namespace App\Http\Controllers\Web;

use App\Enums\PurchaseStatus;
use App\Http\Controllers\Controller;
use App\Models\AccountMovement;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\CashService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Reescrito en Fase 16 para el negocio mayorista: reemplaza las metricas de
 * reparaciones/InventoryItem (especificas de celulares, ver Fase 1) por
 * Compras a proveedores (Fase 12), Stock de catalogo (Fase 6) y Cuenta
 * corriente (Fase 13). De paso se corrigieron dos bugs de compatibilidad
 * MySQL -> Postgres heredados de Vortex que nunca se habian probado contra
 * la base real: HOUR() no existe en Postgres (usar EXTRACT), y los strings
 * en DB::raw() estaban con comillas dobles -- en Postgres eso es un
 * identificador entre comillas, no un literal, y rompe la query.
 */
class ReportController extends Controller
{
    public function index(Request $request)
    {
        $orgId = auth()->user()->organization_id;
        $range = $request->range ?? 'month';

        [$startDate, $endDate] = $this->getDateRange($range, $request);

        // ─── KPIs Principales ───────────────────────────────────────────
        $totalSales = Sale::where('organization_id', $orgId)
            ->whereBetween('sold_at', [$startDate, $endDate])
            ->sum('total');

        $totalProfit = Sale::where('organization_id', $orgId)
            ->whereBetween('sold_at', [$startDate, $endDate])
            ->sum('profit_total');

        $newClients = Client::where('organization_id', $orgId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $totalOrders = Sale::where('organization_id', $orgId)
            ->whereBetween('sold_at', [$startDate, $endDate])
            ->count();

        $avgTicket = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        $marginPercent = $totalSales > 0 ? round(($totalProfit / $totalSales) * 100, 1) : 0;

        // ─── Compras a Proveedores (Fase 12) ─────────────────────────────
        $purchasesTotal = Purchase::where('organization_id', $orgId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', PurchaseStatus::RECEIVED)
            ->sum('total');

        $purchasesCount = Purchase::where('organization_id', $orgId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', PurchaseStatus::RECEIVED)
            ->count();

        // ─── Curva de Ingresos (Ventas por día) ──────────────────────────
        $salesByDay = Sale::where('organization_id', $orgId)
            ->whereBetween('sold_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(sold_at) as date'), DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy(DB::raw('DATE(sold_at)'))
            ->orderBy('date')
            ->get();

        // ─── Ventas por Sucursal ─────────────────────────────────────────
        $salesByBranch = Sale::where('sales.organization_id', $orgId)
            ->whereBetween('sold_at', [$startDate, $endDate])
            ->leftJoin('branches', 'sales.branch_id', '=', 'branches.id')
            ->select(
                DB::raw("COALESCE(branches.name, 'Sede Principal') as branch_name"),
                DB::raw('SUM(sales.total) as total'),
                DB::raw('SUM(sales.profit_total) as profit'),
                DB::raw('COUNT(sales.id) as count')
            )
            ->groupBy('sales.branch_id', 'branches.name')
            ->orderByDesc('total')
            ->get();

        // ─── Ventas por Vendedor ─────────────────────────────────────────
        $salesBySeller = Sale::where('sales.organization_id', $orgId)
            ->whereBetween('sold_at', [$startDate, $endDate])
            ->leftJoin('users', 'sales.seller_id', '=', 'users.id')
            ->select(
                DB::raw("COALESCE(users.name, 'Admin') as seller_name"),
                DB::raw('SUM(sales.total) as total'),
                DB::raw('SUM(sales.profit_total) as profit'),
                DB::raw('COUNT(sales.id) as count')
            )
            ->groupBy('sales.seller_id', 'users.name')
            ->orderByDesc('total')
            ->get();

        // ─── Facturación por Hora (hoy) ───────────────────────────────────
        // EXTRACT(HOUR FROM ...), no HOUR(...) (esa es sintaxis MySQL, no existe en Postgres).
        // Se repite la expresion completa en groupBy/orderBy: Postgres no admite
        // agrupar por el alias de una columna calculada, a diferencia de MySQL.
        $todayBilling = Sale::where('organization_id', $orgId)
            ->whereDate('sold_at', today())
            ->select(
                DB::raw('EXTRACT(HOUR FROM sold_at)::integer as hour'),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy(DB::raw('EXTRACT(HOUR FROM sold_at)'))
            ->orderBy(DB::raw('EXTRACT(HOUR FROM sold_at)'))
            ->get();

        // ─── Productos más vendidos ──────────────────────────────────────
        $topProducts = SaleItem::whereHas('sale', function ($q) use ($orgId, $startDate, $endDate) {
                $q->where('organization_id', $orgId)
                  ->whereBetween('sold_at', [$startDate, $endDate]);
            })
            ->select(
                'item_name',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(line_total) as total_revenue'),
                DB::raw('COUNT(DISTINCT sale_id) as sale_count')
            )
            ->groupBy('item_name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        // ─── Métodos de Pago ─────────────────────────────────────────────
        $paymentMethods = Payment::where('payments.organization_id', $orgId)
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->select(
                'method',
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('method')
            ->orderByDesc('total')
            ->get();

        $totalPayments = $paymentMethods->isNotEmpty() ? ($paymentMethods->sum('total') ?: 1) : 1;

        // ─── Compras por Proveedor ────────────────────────────────────────
        $purchasesBySupplier = Purchase::where('purchases.organization_id', $orgId)
            ->whereBetween('purchases.created_at', [$startDate, $endDate])
            ->where('purchases.status', PurchaseStatus::RECEIVED)
            ->leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->select(
                DB::raw("COALESCE(suppliers.business_name, 'Sin proveedor') as supplier_name"),
                DB::raw('SUM(purchases.total) as total'),
                DB::raw('COUNT(purchases.id) as count')
            )
            ->groupBy('purchases.supplier_id', 'suppliers.business_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // ─── Cuenta Corriente: deudores y total por cobrar (Fase 13) ─────
        // Se agrupa por cliente y se filtra en PHP (no HAVING con subconsulta):
        // mismo razonamiento de bajo volumen que AccountService::balanceFor.
        $clientBalances = AccountMovement::where('account_movements.organization_id', $orgId)
            ->join('clients', 'clients.id', '=', 'account_movements.client_id')
            ->select(
                'clients.id as client_id',
                DB::raw('COALESCE(clients.business_name, clients.full_name) as client_name'),
                DB::raw('SUM(account_movements.amount) as balance')
            )
            ->groupBy('clients.id', 'clients.business_name', 'clients.full_name')
            ->get()
            ->filter(fn ($row) => (float) $row->balance > 0)
            ->sortByDesc('balance')
            ->values();

        $totalReceivable = (float) $clientBalances->sum('balance');
        $topDebtors = $clientBalances->take(5);

        // ─── Stock de Catálogo (Fase 6) ───────────────────────────────────
        $stockRows = DB::table('product_stocks')
            ->join('products', 'products.id', '=', 'product_stocks.product_id')
            ->where('products.organization_id', $orgId)
            ->select('product_stocks.quantity', 'products.retail_price', 'products.wholesale_price', 'products.cost')
            ->get();

        $stockAvailable = $stockRows->where('quantity', '>', 0)->count();
        $stockValue = $stockRows->sum(fn ($r) => $r->quantity * (float) ($r->retail_price ?? $r->wholesale_price ?? 0));
        $stockCost = $stockRows->sum(fn ($r) => $r->quantity * (float) ($r->cost ?? 0));

        // ─── Clientes Top ────────────────────────────────────────────────
        $topClients = Sale::where('sales.organization_id', $orgId)
            ->whereBetween('sold_at', [$startDate, $endDate])
            ->leftJoin('clients', 'sales.client_id', '=', 'clients.id')
            ->select(
                DB::raw("COALESCE(clients.full_name, 'Mostrador') as client_name"),
                DB::raw('SUM(sales.total) as total'),
                DB::raw('COUNT(sales.id) as count')
            )
            ->groupBy('sales.client_id', 'clients.full_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // ─── Últimas ventas recientes ────────────────────────────────────
        $recentSales = Sale::where('sales.organization_id', $orgId)
            ->leftJoin('clients', 'sales.client_id', '=', 'clients.id')
            ->leftJoin('users', 'sales.seller_id', '=', 'users.id')
            ->leftJoin('branches', 'sales.branch_id', '=', 'branches.id')
            ->select(
                'sales.id',
                'sales.sale_number',
                'sales.total',
                'sales.sold_at',
                'sales.status',
                DB::raw("COALESCE(clients.full_name, 'Mostrador') as client_name"),
                DB::raw("COALESCE(users.name, 'Admin') as seller_name"),
                DB::raw("COALESCE(branches.name, 'Matriz') as branch_name")
            )
            ->orderByDesc('sales.sold_at')
            ->limit(8)
            ->get();

        return view('reports.index', compact(
            'totalSales', 'totalProfit', 'newClients',
            'totalOrders', 'avgTicket', 'marginPercent',
            'purchasesTotal', 'purchasesCount',
            'salesByDay', 'salesByBranch', 'salesBySeller',
            'todayBilling', 'topProducts', 'paymentMethods', 'totalPayments',
            'purchasesBySupplier', 'totalReceivable', 'topDebtors',
            'stockAvailable', 'stockValue', 'stockCost',
            'topClients', 'recentSales', 'range'
        ));
    }

    /**
     * API endpoint para datos en tiempo real (polling)
     */
    public function realtime(Request $request)
    {
        $orgId = auth()->user()->organization_id;

        $todayTotal = Sale::where('organization_id', $orgId)
            ->whereDate('sold_at', today())
            ->sum('total');

        $todayCount = Sale::where('organization_id', $orgId)
            ->whereDate('sold_at', today())
            ->count();

        $todayPurchases = Purchase::where('organization_id', $orgId)
            ->whereDate('created_at', today())
            ->where('status', PurchaseStatus::RECEIVED)
            ->count();

        $cashSession = app(CashService::class)->currentSession($orgId);

        // Última venta
        $lastSale = Sale::where('sales.organization_id', $orgId)
            ->leftJoin('clients', 'sales.client_id', '=', 'clients.id')
            ->select('sales.total', 'sales.sold_at', DB::raw("COALESCE(clients.full_name, 'Mostrador') as client_name"))
            ->latest('sales.sold_at')
            ->first();

        return response()->json([
            'today_total'      => $todayTotal,
            'today_count'      => $todayCount,
            'today_purchases'  => $todayPurchases,
            'cash_session_open' => $cashSession !== null,
            'cash_balance'     => $cashSession ? app(CashService::class)->balanceFor($cashSession) : null,
            'last_sale'        => $lastSale,
            'timestamp'        => now()->format('H:i:s'),
        ]);
    }

    private function getDateRange(string $range, Request $request): array
    {
        if ($range === 'custom' && $request->filled('start_date') && $request->filled('end_date')) {
            try {
                return [
                    Carbon::parse($request->start_date)->startOfDay(),
                    Carbon::parse($request->end_date)->endOfDay()
                ];
            } catch (\Exception $e) {}
        }

        $endDate = now()->endOfDay();

        $startDate = match($range) {
            'today' => now()->startOfDay(),
            'week'  => now()->startOfWeek(),
            'year'  => now()->startOfYear(),
            default => now()->startOfMonth(), // 'month'
        };

        return [$startDate, $endDate];
    }
}
