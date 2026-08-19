<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InventoryItemResource;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $items = InventoryItem::query()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('model', 'like', "%{$search}%")
                        ->orWhere('imei', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%");
                });
            })
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->category, fn($q, $cat) => $q->where('category', $cat))
            ->paginate($request->per_page ?? 15);

        return InventoryItemResource::collection($items);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'brand' => 'required|string',
            'model' => 'required|string',
            'storage' => 'nullable|string',
            'color' => 'nullable|string',
            'imei' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'battery_health' => 'nullable|integer',
            'cosmetic_condition' => 'nullable|string',
            'purchase_price' => 'nullable|numeric',
            'sale_price' => 'nullable|numeric',
            'currency' => 'nullable|string|size:3',
            'status' => 'nullable|string',
            'stock_type' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $item = InventoryItem::create($validated);

        return new InventoryItemResource($item);
    }

    public function show(InventoryItem $inventory)
    {
        return new InventoryItemResource($inventory);
    }

    public function update(Request $request, InventoryItem $inventory)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'model' => 'required|string',
            'storage' => 'nullable|string',
            'color' => 'nullable|string',
            'imei' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'battery_health' => 'nullable|integer',
            'status' => 'nullable|string',
            'purchase_price' => 'nullable|numeric',
            'sale_price' => 'nullable|numeric',
        ]);

        $inventory->update($validated);

        return new InventoryItemResource($inventory);
    }

    public function destroy(InventoryItem $inventory)
    {
        $inventory->delete();
        return response()->json(['message' => 'Item eliminado del inventario.']);
    }
}
