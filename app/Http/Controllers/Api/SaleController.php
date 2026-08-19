<?php

namespace App\Http\Controllers\Api;

use App\Enums\InventoryStatus;
use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $sales = Sale::with(['client', 'seller'])
            ->orderBy('sold_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($sales);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.unit_price' => 'required|numeric',
            'currency' => 'required|string|size:3',
            'exchange_rate' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            $subtotal = 0;
            $costTotal = 0;

            foreach ($validated['items'] as $itemData) {
                $subtotal += $itemData['unit_price'];
                $invItem = InventoryItem::findOrFail($itemData['inventory_item_id']);
                $costTotal += $invItem->purchase_price ?? 0;
            }

            $total = $subtotal - ($validated['discount'] ?? 0);

            $sale = Sale::create([
                'client_id' => $validated['client_id'] ?? null,
                'seller_id' => $request->user()->id,
                'sale_number' => 'SALE-' . strtoupper(Str::random(6)),
                'status' => 'completed',
                'currency' => $validated['currency'],
                'exchange_rate' => $validated['exchange_rate'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $validated['discount'] ?? 0,
                'total' => $total,
                'cost_total' => $costTotal,
                'profit_total' => $total - $costTotal,
                'notes' => $validated['notes'] ?? null,
                'sold_at' => now(),
            ]);

            foreach ($validated['items'] as $itemData) {
                $invItem = InventoryItem::findOrFail($itemData['inventory_item_id']);

                $sale->items()->create([
                    'inventory_item_id' => $invItem->id,
                    'item_name' => $invItem->model,
                    'unit_cost' => $invItem->purchase_price ?? 0,
                    'unit_price' => $itemData['unit_price'],
                    'quantity' => 1,
                    'line_total' => $itemData['unit_price'],
                ]);

                // Update inventory status
                $invItem->update(['status' => InventoryStatus::SOLD]);
            }

            return response()->json($sale->load('items'));
        });
    }

    public function show(Sale $sale)
    {
        return response()->json($sale->load(['client', 'items', 'payments']));
    }
}
