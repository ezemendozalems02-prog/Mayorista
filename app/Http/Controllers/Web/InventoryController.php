<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Enums\UserRole;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $orgId = auth()->user()->organization_id;

        $items = InventoryItem::where('organization_id', $orgId)
            ->where('status', '!=', \App\Enums\InventoryStatus::ARCHIVED->value)
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('model', 'like', "%{$search}%")
                        ->orWhere('imei', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->category, function ($query, $category) {
                $query->where('category', $category);
            })
            ->when($request->branch_id, function ($query, $branchId) {
                $query->where('branch_id', $branchId);
            })
            ->latest()
            ->get();

        $grouped = $items->groupBy(function ($item) {
            return "{$item->brand}_{$item->model}_{$item->storage}_{$item->color}";
        });

        $perPage = 15;
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $pagedData = $grouped->slice(($currentPage - 1) * $perPage, $perPage)->all();

        $items = new \Illuminate\Pagination\LengthAwarePaginator($pagedData, count($grouped), $perPage, $currentPage, [
            'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
            'query' => $request->query(),
        ]);

        $branches = \App\Models\Branch::where('organization_id', $orgId)->where('is_active', true)->get();

        return view('inventory.index', compact('items', 'branches'));
    }

    public function show(InventoryItem $inventory)
    {
        return view('inventory.show', ['item' => $inventory]);
    }

    public function create()
    {
        $orgId = auth()->user()->organization_id;
        $branches = \App\Models\Branch::where('organization_id', $orgId)->where('is_active', true)->get();
        return view('inventory.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $orgId = auth()->user()->organization_id;
        $user = auth()->user();

        $branchExistsRule = Rule::exists('branches', 'id');
        if ($user && $user->role !== UserRole::SUPER_ADMIN) {
            $branchExistsRule = $branchExistsRule->where('organization_id', $orgId);
        }

        $validated = $request->validate([
            'category'          => 'required|string',
            'brand'             => 'required|string',
            'model'             => 'required|string',
            'storage'           => 'nullable|string',
            'color'             => 'nullable|string',
            'imei'              => 'nullable|string|unique:inventory_items,imei,NULL,id,organization_id,' . $orgId,
            'serial_number'     => 'nullable|string',
            'battery_health'    => 'nullable|integer|min:0|max:100',
            'cosmetic_condition' => 'nullable|string',
            'purchase_price'    => 'required|numeric|min:0',
            'sale_price'        => 'required|numeric|min:0',
            'currency'          => 'required|string|max:3',
            'status'            => 'required|string',
            'stock_type'        => 'required|string',
            'branch_id'         => ['nullable', $branchExistsRule],
            'notes'             => 'nullable|string',
        ]);

        $organization = auth()->user()->organization;
        $validated['organization_id'] = $orgId;

        // Check stock limit for Basic plan
        if (!$organization->hasFeature('inventory_unlimited')) {
            $currentStock = InventoryItem::where('organization_id', $orgId)
                ->where('status', '!=', \App\Enums\InventoryStatus::ARCHIVED->value)
                ->count();
            
            if ($currentStock >= 500) {
                return back()->withErrors(['error' => 'Has alcanzado el límite de 500 equipos para tu plan actual. Por favor, actualiza a Plan Pro para stock ilimitado.'])->withInput();
            }
        }

        InventoryItem::create($validated);

        return redirect()->route('inventory.index')->with('success', 'Equipo ingresado al sistema.');
    }

    public function edit(InventoryItem $inventory)
    {
        $orgId = auth()->user()->organization_id;
        $branches = \App\Models\Branch::where('organization_id', $orgId)->where('is_active', true)->get();
        return view('inventory.edit', ['item' => $inventory, 'branches' => $branches]);
    }

    public function update(Request $request, InventoryItem $inventory)
    {
        $orgId = auth()->user()->organization_id;

        $validated = $request->validate([
            'category' => 'required|string',
            'brand' => 'required|string',
            'model' => 'required|string',
            'storage' => 'nullable|string',
            'color' => 'nullable|string',
            'imei' => 'nullable|string|unique:inventory_items,imei,' . $inventory->id . ',id,organization_id,' . $orgId,
            'serial_number' => 'nullable|string',
            'battery_health' => 'nullable|integer|min:0|max:100',
            'cosmetic_condition' => 'nullable|string',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'status' => 'required|string',
            'stock_type' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $inventory->update($validated);

        return redirect()->route('inventory.index')->with('success', 'Equipo actualizado correctamente.');
    }

    public function destroy(InventoryItem $inventory)
    {
        $inventory->delete();
        return redirect()->route('inventory.index')->with('success', 'Equipo eliminado (Soft Delete).');
    }
}
