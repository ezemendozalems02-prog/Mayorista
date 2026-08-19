<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Client;
use App\Models\InventoryItem;
use App\Models\Payment;
use App\Models\Repair;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $repairCount = Repair::where('organization_id', $orgId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $newClients = Client::where('organization_id', $orgId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $totalOrders = Sale::where('organization_id', $orgId)
            ->whereBetween('sold_at', [$startDate, $endDate])
            ->count();

        $avgTicket = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        $repairRevenue = Repair::where('organization_id', $orgId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', \App\Enums\RepairStatus::DELIVERED)
            ->sum('final_cost');

        $marginPercent = $totalSales > 0 ? round(($totalProfit / $totalSales) * 100, 1) : 0;

        // ─── Curva de Ingresos (Ventas por día) ──────────────────────────
        $salesByDay = Sale::where('organization_id', $orgId)
            ->whereBetween('sold_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(sold_at) as date'), DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // ─── Ventas por Sucursal ─────────────────────────────────────────
        $salesByBranch = Sale::where('sales.organization_id', $orgId)
            ->whereBetween('sold_at', [$startDate, $endDate])
            ->leftJoin('branches', 'sales.branch_id', '=', 'branches.id')
            ->select(
                DB::raw('COALESCE(branches.name, "Sede Principal") as branch_name'),
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
                DB::raw('COALESCE(users.name, "Admin") as seller_name'),
                DB::raw('SUM(sales.total) as total'),
                DB::raw('SUM(sales.profit_total) as profit'),
                DB::raw('COUNT(sales.id) as count')
            )
            ->groupBy('sales.seller_id', 'users.name')
            ->orderByDesc('total')
            ->get();

        // ─── Facturación Diaria (hoy) ────────────────────────────────────
        $todayBilling = Sale::where('organization_id', $orgId)
            ->whereDate('sold_at', today())
            ->select(
                DB::raw('HOUR(sold_at) as hour'),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('hour')
            ->orderBy('hour')
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

        if ($paymentMethods->isNotEmpty()) {
            $totalPayments = $paymentMethods->sum('total') ?: 1;
        } else {
            $totalPayments = 1;
        }

        // ─── Reparaciones por Técnico ────────────────────────────────────
        $repairsByTechnician = Repair::where('repairs.organization_id', $orgId)
            ->whereBetween('repairs.created_at', [$startDate, $endDate])
            ->leftJoin('technicians', 'repairs.technician_id', '=', 'technicians.id')
            ->select(
                DB::raw('COALESCE(technicians.full_name, "Sin técnico") as tech_name'),
                DB::raw('COUNT(repairs.id) as count'),
                DB::raw('SUM(repairs.final_cost) as revenue'),
                DB::raw('SUM(CASE WHEN repairs.status IN ("ready", "delivered") THEN 1 ELSE 0 END) as completed')
            )
            ->groupBy('repairs.technician_id', 'technicians.full_name')
            ->orderByDesc('count')
            ->get();

        // ─── Estado Reparaciones ─────────────────────────────────────────
        $repairStatuses = Repair::where('organization_id', $orgId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // ─── Inventario (Stock actual) ───────────────────────────────────
        $stockAvailable = InventoryItem::where('organization_id', $orgId)
            ->where('status', \App\Enums\InventoryStatus::IN_STOCK)
            ->count();

        $stockValue = InventoryItem::where('organization_id', $orgId)
            ->where('status', \App\Enums\InventoryStatus::IN_STOCK)
            ->sum('sale_price');

        $stockCost = InventoryItem::where('organization_id', $orgId)
            ->where('status', \App\Enums\InventoryStatus::IN_STOCK)
            ->sum('purchase_price');

        // ─── Clientes Top ────────────────────────────────────────────────
        $topClients = Sale::where('sales.organization_id', $orgId)
            ->whereBetween('sold_at', [$startDate, $endDate])
            ->leftJoin('clients', 'sales.client_id', '=', 'clients.id')
            ->select(
                DB::raw('COALESCE(clients.full_name, "Sin nombre") as client_name'),
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
                DB::raw('COALESCE(clients.full_name, "Mostrador") as client_name'),
                DB::raw('COALESCE(users.name, "Admin") as seller_name'),
                DB::raw('COALESCE(branches.name, "Matriz") as branch_name')
            )
            ->orderByDesc('sales.sold_at')
            ->limit(8)
            ->get();

        return view('reports.index', compact(
            'totalSales', 'totalProfit', 'repairCount', 'newClients',
            'totalOrders', 'avgTicket', 'repairRevenue', 'marginPercent',
            'salesByDay', 'salesByBranch', 'salesBySeller',
            'todayBilling', 'topProducts', 'paymentMethods', 'totalPayments',
            'repairsByTechnician', 'repairStatuses',
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

        $todayRepairs = Repair::where('organization_id', $orgId)
            ->whereDate('created_at', today())
            ->count();

        $pendingRepairs = Repair::where('organization_id', $orgId)
            ->whereIn('status', [
                \App\Enums\RepairStatus::PENDING,
                \App\Enums\RepairStatus::DIAGNOSIS,
                \App\Enums\RepairStatus::QUOTED,
                \App\Enums\RepairStatus::APPROVED,
                \App\Enums\RepairStatus::IN_PROGRESS,
            ])
            ->count();

        // Última venta
        $lastSale = Sale::where('sales.organization_id', $orgId)
            ->leftJoin('clients', 'sales.client_id', '=', 'clients.id')
            ->select('sales.total', 'sales.sold_at', DB::raw('COALESCE(clients.full_name, "Mostrador") as client_name'))
            ->latest('sales.sold_at')
            ->first();

        return response()->json([
            'today_total'    => $todayTotal,
            'today_count'    => $todayCount,
            'today_repairs'  => $todayRepairs,
            'pending_repairs' => $pendingRepairs,
            'last_sale'      => $lastSale,
            'timestamp'      => now()->format('H:i:s'),
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
