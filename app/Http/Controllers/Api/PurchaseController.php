<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PurchaseResource;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PurchaseController extends Controller
{
    public function __construct(private PurchaseService $service)
    {
    }

    public function index(Request $request)
    {
        $purchases = Purchase::query()
            ->with('supplier')
            ->withCount('items')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('supplier_id'), fn ($q) => $q->where('supplier_id', $request->supplier_id))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return PurchaseResource::collection($purchases);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', Rule::exists('suppliers', 'id')->where('organization_id', Auth::user()->organization_id)],
            'items' => 'required|array|min:1',
            'items.*.product_id' => ['required', Rule::exists('products', 'id')->where('organization_id', Auth::user()->organization_id)],
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $supplier = Supplier::findOrFail($validated['supplier_id']);

        $purchase = $this->service->create(
            supplier: $supplier,
            items: $validated['items'],
            user: $request->user(),
            notes: $validated['notes'] ?? null,
            discount: (float) ($validated['discount'] ?? 0),
        );

        return new PurchaseResource($purchase->load(['supplier', 'items.product']));
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'creator', 'items.product']);

        return new PurchaseResource($purchase);
    }

    public function receive(Purchase $purchase)
    {
        try {
            $this->service->receive($purchase);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return new PurchaseResource($purchase->fresh()->load(['supplier', 'items.product']));
    }

    public function cancel(Purchase $purchase)
    {
        try {
            $this->service->cancel($purchase);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return new PurchaseResource($purchase->fresh());
    }
}
