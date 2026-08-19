<?php

namespace App\Http\Controllers\Web;

use App\Enums\StockMovementType;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class StockController extends Controller
{
    public function __construct(private StockService $stockService)
    {
    }

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
            ->paginate(15)
            ->withQueryString();

        return view('stock.index', compact('products'));
    }

    public function movements(Product $product)
    {
        $movements = $product->stockMovements()
            ->with('user')
            ->latest('created_at')
            ->paginate(20);

        return view('stock.movements', compact('product', 'movements'));
    }

    public function adjust(Request $request, Product $product)
    {
        $validated = $request->validate([
            'type' => ['required', new Enum(StockMovementType::class)],
            'direction' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        $delta = $validated['quantity'] * ($validated['direction'] === 'out' ? -1 : 1);

        $this->stockService->recordMovement(
            product: $product,
            quantityDelta: $delta,
            type: StockMovementType::from($validated['type']),
            notes: $validated['notes'] ?? null,
        );

        return redirect()->route('stock.movements', $product)->with('success', 'Movimiento de stock registrado correctamente.');
    }
}
