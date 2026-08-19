<?php

namespace App\Http\Controllers\Api;

use App\Enums\InventoryStatus;
use App\Enums\RepairStatus;
use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Repair;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $orgId = $request->user()->organization_id;

        // KPI Metrics
        $salesMonth = Sale::where('organization_id', $orgId)
            ->whereMonth('sold_at', now()->month)
            ->sum('total');

        $profitMonth = Sale::where('organization_id', $orgId)
            ->whereMonth('sold_at', now()->month)
            ->sum('profit_total');

        $activeRepairs = Repair::where('organization_id', $orgId)
            ->whereNotIn('status', [RepairStatus::DELIVERED, RepairStatus::CANCELLED])
            ->count();

        $stockCount = InventoryItem::where('organization_id', $orgId)
            ->where('status', InventoryStatus::IN_STOCK)
            ->count();

        // Recent Activity
        $recentSales = Sale::with('client')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentRepairs = Repair::with('client')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'metrics' => [
                'sales_month' => $salesMonth,
                'profit_month' => $profitMonth,
                'active_repairs' => $activeRepairs,
                'stock_count' => $stockCount,
            ],
            'recent_sales' => $recentSales,
            'recent_repairs' => $recentRepairs,
        ]);
    }
}
