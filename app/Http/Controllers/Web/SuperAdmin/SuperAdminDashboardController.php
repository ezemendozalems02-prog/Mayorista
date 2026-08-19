<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Models\Sale;
use App\Models\Repair;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuperAdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_organizations' => Organization::count(),
            'active_organizations' => Organization::where('is_active', true)->count(),
            'total_users' => User::count(),
            'total_sales' => Sale::withoutGlobalScopes()->count(),
            'total_revenue' => Sale::withoutGlobalScopes()->sum('total'),
            'total_repairs' => Repair::withoutGlobalScopes()->count(),
        ];

        $latest_organizations = Organization::withCount(['users', 'branches'])
            ->latest()
            ->limit(5)
            ->get();

        $revenue_by_org = Sale::withoutGlobalScopes()
            ->join('organizations', 'sales.organization_id', '=', 'organizations.id')
            ->select('organizations.name', DB::raw('SUM(sales.total) as total'))
            ->groupBy('organizations.id', 'organizations.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return view('super-admin.dashboard', compact('stats', 'latest_organizations', 'revenue_by_org'));
    }
}
