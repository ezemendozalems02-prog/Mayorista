<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $organizations = Organization::withCount(['users', 'branches', 'sales', 'repairs'])
            ->when($request->search, function($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('document_number', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(20);

        return view('super-admin.organizations.index', compact('organizations'));
    }

    public function show(Organization $organization)
    {
        $organization->load(['users', 'branches']);
        
        $stats = [
            'total_sales' => $organization->sales()->count(),
            'total_revenue' => $organization->sales()->sum('total'),
            'total_repairs' => $organization->repairs()->count(),
            'total_items' => $organization->inventoryItems()->count(),
        ];

        return view('super-admin.organizations.show', compact('organization', 'stats'));
    }

    public function toggleStatus(Organization $organization)
    {
        $organization->update([
            'is_active' => !$organization->is_active
        ]);

        $status = $organization->is_active ? 'activado' : 'desactivado';
        return back()->with('success', "El negocio {$organization->name} ha sido {$status} correctamente.");
    }

    public function updatePlan(Request $request, Organization $organization)
    {
        $request->validate([
            'plan' => 'required|string'
        ]);

        $organization->update([
            'plan' => $request->plan
        ]);

        return back()->with('success', "Plan del negocio {$organization->name} actualizado a {$request->plan}.");
    }
}
