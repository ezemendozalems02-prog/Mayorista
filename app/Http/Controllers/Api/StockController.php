<?php

namespace App\Http\Controllers\Api;

use App\Enums\StockMovementType;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\StockMovementResource;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class StockController extends Controller
{
    public function __construct(private StockService $stockService)
    {
    }

    /**
     * GET /stock — listado de productos con su stock actual, para el panel de inventario.
     */
    public function index(Request $request)
    {
        $products = Product::query()
            ->with(['category', 'brand', 'stock'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('internal_code', 'ilike', "%{$search}%")
                        ->orWhere('barcode', 'ilike', "%{$search}%");
                });
            })
            ->when($request->boolean('low_stock'), function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('stock', fn ($s) => $s->whereColumn('quantity', '<=', 'products.min_stock'))
                        ->orWhereDoesntHave('stock');
                })->where('min_stock', '>', 0);
            })
            ->orderBy('name')
            ->paginate($request->per_page ?? 15);

        return ProductResource::collection($products);
    }

    /**
     * GET /products/{product}/stock-movements — historial paginado del ledger.
     */
    public function movements(Request $request, Product $product)
    {
        $movements = $product->stockMovements()
            ->with('user')
            ->latest('created_at')
            ->paginate($request->per_page ?? 20);

        return StockMovementResource::collection($movements);
    }

    /**
     * POST /products/{product}/stock-movements — registra un movimiento manual
     * (ajuste, conteo fisico, devolucion, etc). Las entradas por compra/venta se
     * registran desde sus respectivos modulos (Fases 11/12), no desde aca.
     */
    public function storeMovement(Request $request, Product $product)
    {
        $validated = $request->validate([
            'type' => ['required', new Enum(StockMovementType::class)],
            'quantity_delta' => 'required|integer|not_in:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $movement = $this->stockService->recordMovement(
            product: $product,
            quantityDelta: $validated['quantity_delta'],
            type: StockMovementType::from($validated['type']),
            notes: $validated['notes'] ?? null,
            unitCost: $validated['unit_cost'] ?? null,
        );

        return new StockMovementResource($movement->load('user'));
    }
}
