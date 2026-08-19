<?php

namespace App\Http\Controllers\Web;

use App\Enums\InventoryStatus;
use App\Enums\RepairStatus;
use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Repair;
use App\Models\Sale;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->user()->role === \App\Enums\UserRole::SUPER_ADMIN) {
            return redirect()->route('super-admin.dashboard');
        }

        $orgId = auth()->user()->organization_id;

        // KPI Metrics
        $metrics = [
            'sales_month' => Sale::where('organization_id', $orgId)
                ->whereMonth('sold_at', now()->month)
                ->sum('total'),

            'profit_month' => Sale::where('organization_id', $orgId)
                ->whereMonth('sold_at', now()->month)
                ->sum('profit_total'),

            'active_repairs' => Repair::where('organization_id', $orgId)
                ->whereNotIn('status', [RepairStatus::DELIVERED, RepairStatus::CANCELLED])
                ->count(),

            'stock_count' => InventoryItem::where('organization_id', $orgId)
                ->where('status', InventoryStatus::IN_STOCK)
                ->count(),

            'stock_value' => InventoryItem::where('organization_id', $orgId)
                ->where('status', InventoryStatus::IN_STOCK)
                ->sum('sale_price'),
        ];

        // Recent Activity
        $recent_sales = Sale::where('organization_id', $orgId)
            ->with(['client'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recent_repairs = Repair::where('organization_id', $orgId)
            ->with(['client'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact('metrics', 'recent_sales', 'recent_repairs'));
    }
}
