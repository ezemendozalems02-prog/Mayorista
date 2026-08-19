<?php

namespace App\Http\Controllers\Web;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
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
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('internal_code', 'ilike', "%{$search}%")
                        ->orWhere('barcode', 'ilike', "%{$search}%");
                });
            })
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->category_id))
            ->when($request->filled('brand_id'), fn ($query) => $query->where('brand_id', $request->brand_id))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'brands'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('business_name')->get();

        return view('products.create', compact('categories', 'brands', 'suppliers'));
    }

    public function store(Request $request)
    {
        [$validated, $suppliers] = $this->validateProduct($request);

        DB::transaction(function () use ($validated, $suppliers) {
            $product = Product::create($validated);

            if (! empty($suppliers)) {
                $product->suppliers()->attach($suppliers);
            }
        });

        return redirect()->route('product.index')->with('success', 'Producto creado correctamente.');
    }

    public function edit(Product $product)
    {
        $product->load('suppliers');
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('business_name')->get();

        // Proveedores ya vinculados al producto, indexados por id, para pre-marcar el fieldset.
        $selected = $product->suppliers->mapWithKeys(fn ($s) => [$s->id => [
            'supplier_sku' => $s->pivot->supplier_sku,
            'cost' => $s->pivot->cost,
            'is_primary' => $s->pivot->is_primary,
        ]])->all();

        return view('products.edit', compact('product', 'categories', 'brands', 'suppliers', 'selected'));
    }

    public function update(Request $request, Product $product)
    {
        [$validated, $suppliers] = $this->validateProduct($request, $product);

        DB::transaction(function () use ($product, $validated, $suppliers) {
            $product->update($validated);
            $product->suppliers()->sync($suppliers);
        });

        return redirect()->route('product.index')->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('product.index')->with('success', 'Producto eliminado correctamente.');
    }

    /**
     * Valida el formulario y arma el payload de proveedores (id => pivot) a partir
     * de los checkboxes "suppliers[{id}][selected]" + sku/cost/is_primary por fila.
     */
    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $validated = $request->validate([
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('organization_id', Auth::user()->organization_id)],
            'brand_id' => ['nullable', Rule::exists('brands', 'id')->where('organization_id', Auth::user()->organization_id)],
            'barcode' => [
                'nullable', 'string', 'max:255',
                Rule::unique('products')->where('organization_id', Auth::user()->organization_id)->ignore($product?->id),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
            'retail_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'status' => ['nullable', new Enum(ProductStatus::class)],
            'suppliers' => 'nullable|array',
            'suppliers.*.selected' => 'nullable|boolean',
            'suppliers.*.supplier_sku' => 'nullable|string|max:255',
            'suppliers.*.cost' => 'nullable|numeric|min:0',
            'suppliers.*.is_primary' => 'nullable|boolean',
        ]);

        $suppliersInput = $validated['suppliers'] ?? [];
        unset($validated['suppliers']);

        $suppliers = [];
        foreach ($suppliersInput as $supplierId => $row) {
            if (empty($row['selected'])) {
                continue;
            }

            $suppliers[$supplierId] = [
                'supplier_sku' => $row['supplier_sku'] ?? null,
                'cost' => $row['cost'] ?? null,
                'is_primary' => ! empty($row['is_primary']),
            ];
        }

        // Los ids de proveedor vienen de un formulario (nombres de checkbox); si alguno
        // no pertenece a la organizacion, fallamos con un error prolijo en vez de dejar
        // que reviente la FK dentro de la transaccion.
        if (! empty($suppliers)) {
            $validCount = \App\Models\Supplier::whereIn('id', array_keys($suppliers))->count();
            if ($validCount !== count($suppliers)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'suppliers' => 'Uno o mas proveedores seleccionados no son validos.',
                ]);
            }
        }

        return [$validated, $suppliers];
    }
}
