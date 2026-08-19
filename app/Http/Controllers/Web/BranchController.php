<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{
    public function index()
    {
        $orgId = Auth::user()->organization_id;
        $branches = Branch::where('organization_id', $orgId)
            ->withCount('inventoryItems')
            ->withCount('sales')
            ->withCount('repairs')
            ->latest()
            ->get();

        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        return view('branches.create');
    }

    public function store(Request $request)
    {
        $orgId = Auth::user()->organization_id;

        $validated = $request->validate([
            'name'         => 'required|string|max:120',
            'address'      => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:30',
            'email'        => 'nullable|email|max:120',
            'manager_name' => 'nullable|string|max:120',
            'is_active'    => 'boolean',
            'notes'        => 'nullable|string',
        ]);

        $validated['organization_id'] = $orgId;
        $validated['is_active'] = $request->boolean('is_active', true);

        // If this is the first branch, mark it as main
        $count = Branch::where('organization_id', $orgId)->count();
        if ($count === 0) {
            $validated['is_main'] = true;
        }

        Branch::create($validated);

        return redirect()->route('branch.index')->with('success', 'Sucursal creada correctamente.');
    }

    public function show(Branch $branch)
    {
        $this->authorizeOrgBranch($branch);

        $branch->load(['inventoryItems', 'repairs', 'sales']);

        $stats = [
            'stock'         => $branch->inventoryItems()->where('status', '!=', 'sold')->count(),
            'sales_month'   => $branch->sales()->whereMonth('created_at', now()->month)->sum('total'),
            'repairs_open'  => $branch->repairs()->whereNotIn('status', ['delivered'])->count(),
        ];

        return view('branches.show', compact('branch', 'stats'));
    }

    public function edit(Branch $branch)
    {
        $this->authorizeOrgBranch($branch);
        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $this->authorizeOrgBranch($branch);

        $validated = $request->validate([
            'name'         => 'required|string|max:120',
            'address'      => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:30',
            'email'        => 'nullable|email|max:120',
            'manager_name' => 'nullable|string|max:120',
            'is_active'    => 'boolean',
            'notes'        => 'nullable|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $branch->update($validated);

        return redirect()->route('branch.index')->with('success', 'Sucursal actualizada correctamente.');
    }

    public function setMain(Branch $branch)
    {
        $this->authorizeOrgBranch($branch);
        $orgId = Auth::user()->organization_id;

        // Remove current main
        Branch::where('organization_id', $orgId)->update(['is_main' => false]);
        $branch->update(['is_main' => true]);

        return redirect()->route('branch.index')->with('success', "\"{$branch->name}\" es ahora la sucursal principal.");
    }

    public function destroy(Branch $branch)
    {
        $this->authorizeOrgBranch($branch);

        if ($branch->is_main) {
            return redirect()->route('branch.index')->with('error', 'No podés eliminar la sucursal principal. Establecé otra como principal primero.');
        }

        $branch->delete();
        return redirect()->route('branch.index')->with('success', 'Sucursal eliminada.');
    }

    /**
     * Ensure the branch belongs to the authenticated user's organization.
     */
    private function authorizeOrgBranch(Branch $branch): void
    {
        abort_if($branch->organization_id !== Auth::user()->organization_id, 403);
    }
}
