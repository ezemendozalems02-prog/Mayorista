<?php

namespace App\Http\Controllers\Api;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->with(['category', 'brand', 'stock'])
            ->when($request->search, function ($query, $search) {
                // ilike (no like): Postgres es case-sensitive por defecto, a diferencia de MySQL.
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('internal_code', 'ilike', "%{$search}%")
                        ->orWhere('barcode', 'ilike', "%{$search}%");
                });
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })
            ->when($request->filled('brand_id'), function ($query) use ($request) {
                $query->where('brand_id', $request->brand_id);
            })
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->whereHas('suppliers', fn ($q) => $q->where('suppliers.id', $request->supplier_id));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->boolean('low_stock'), function ($query) {
                // Productos cuyo stock actual esta en o por debajo del minimo configurado.
                // Sin fila en product_stocks todavia = stock 0, tambien cuenta como bajo.
                $query->where(function ($q) {
                    $q->whereHas('stock', fn ($s) => $s->whereColumn('quantity', '<=', 'products.min_stock'))
                        ->orWhereDoesntHave('stock');
                })->where('min_stock', '>', 0);
            })
            ->orderBy('name')
            ->paginate($request->per_page ?? 15);

        return ProductResource::collection($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('organization_id', Auth::user()->organization_id)],
            'brand_id' => ['nullable', Rule::exists('brands', 'id')->where('organization_id', Auth::user()->organization_id)],
            'barcode' => [
                'nullable', 'string', 'max:255',
                Rule::unique('products')->where('organization_id', Auth::user()->organization_id),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
            'retail_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'status' => ['nullable', new Enum(ProductStatus::class)],
            'suppliers' => 'nullable|array',
            'suppliers.*.id' => ['required', Rule::exists('suppliers', 'id')->where('organization_id', Auth::user()->organization_id)],
            'suppliers.*.supplier_sku' => 'nullable|string|max:255',
            'suppliers.*.cost' => 'nullable|numeric|min:0',
            'suppliers.*.is_primary' => 'boolean',
        ]);

        $suppliers = $validated['suppliers'] ?? [];
        unset($validated['suppliers']);

        $product = DB::transaction(function () use ($validated, $suppliers) {
            $product = Product::create($validated);

            if (! empty($suppliers)) {
                $product->suppliers()->attach($this->syncPayload($suppliers));
            }

            return $product;
        });

        return new ProductResource($product->load(['category', 'brand', 'suppliers']));
    }

    public function show(Product $product)
    {
        $product->load(['category', 'brand', 'suppliers']);

        return new ProductResource($product);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('organization_id', Auth::user()->organization_id)],
            'brand_id' => ['nullable', Rule::exists('brands', 'id')->where('organization_id', Auth::user()->organization_id)],
            'barcode' => [
                'nullable', 'string', 'max:255',
                Rule::unique('products')->where('organization_id', Auth::user()->organization_id)->ignore($product->id),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
            'retail_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'status' => ['nullable', new Enum(ProductStatus::class)],
            'suppliers' => 'nullable|array',
            'suppliers.*.id' => ['required', Rule::exists('suppliers', 'id')->where('organization_id', Auth::user()->organization_id)],
            'suppliers.*.supplier_sku' => 'nullable|string|max:255',
            'suppliers.*.cost' => 'nullable|numeric|min:0',
            'suppliers.*.is_primary' => 'boolean',
        ]);

        $suppliers = $validated['suppliers'] ?? null;
        unset($validated['suppliers']);

        DB::transaction(function () use ($product, $validated, $suppliers) {
            $product->update($validated);

            if ($suppliers !== null) {
                $product->suppliers()->sync($this->syncPayload($suppliers));
            }
        });

        return new ProductResource($product->load(['category', 'brand', 'suppliers']));
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(['message' => 'Producto eliminado correctamente.']);
    }

    /**
     * Arma el array id => [pivot fields] que espera attach()/sync().
     */
    private function syncPayload(array $suppliers): array
    {
        $payload = [];

        foreach ($suppliers as $supplier) {
            $payload[$supplier['id']] = [
                'supplier_sku' => $supplier['supplier_sku'] ?? null,
                'cost' => $supplier['cost'] ?? null,
                'is_primary' => $supplier['is_primary'] ?? false,
            ];
        }

        return $payload;
    }
}
