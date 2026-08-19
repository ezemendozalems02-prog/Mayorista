<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PriceList;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PriceListController extends Controller
{
    public function index(Request $request)
    {
        $priceLists = PriceList::query()
            ->withCount(['items', 'clients'])
            ->when($request->search, fn ($q, $search) => $q->where('name', 'ilike', "%{$search}%"))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('price-lists.index', compact('priceLists'));
    }

    public function create()
    {
        return view('price-lists.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('price_lists')->where('organization_id', Auth::user()->organization_id),
            ],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $priceList = PriceList::create($validated);

        return redirect()->route('price-list.show', $priceList)->with('success', 'Lista de precios creada. Ahora agregá los productos con precio especial.');
    }

    public function show(Request $request, PriceList $priceList)
    {
        $priceList->load(['items.product']);

        $products = Product::query()
            ->when($request->search, fn ($q, $search) => $q->where(function ($qq) use ($search) {
                $qq->where('name', 'ilike', "%{$search}%")->orWhere('internal_code', 'ilike', "%{$search}%");
            }))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $itemsByProduct = $priceList->items->keyBy('product_id');

        return view('price-lists.show', compact('priceList', 'products', 'itemsByProduct'));
    }

    public function edit(PriceList $priceList)
    {
        return view('price-lists.edit', compact('priceList'));
    }

    public function update(Request $request, PriceList $priceList)
    {
        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('price_lists')->where('organization_id', Auth::user()->organization_id)->ignore($priceList->id),
            ],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $priceList->update($validated);

        return redirect()->route('price-list.index')->with('success', 'Lista de precios actualizada.');
    }

    public function destroy(PriceList $priceList)
    {
        if ($priceList->clients()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay clientes con esta lista de precios asignada.');
        }

        $priceList->delete();

        return redirect()->route('price-list.index')->with('success', 'Lista de precios eliminada.');
    }

    /**
     * Guarda/actualiza el precio especial de un solo producto dentro de la lista.
     * Un precio vacio quita al producto de la lista (vuelve a su precio base).
     */
    public function setItem(Request $request, PriceList $priceList)
    {
        $validated = $request->validate([
            'product_id' => ['required', Rule::exists('products', 'id')->where('organization_id', Auth::user()->organization_id)],
            'price' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($priceList, $validated) {
            if ($validated['price'] === null || $validated['price'] === '') {
                $priceList->items()->where('product_id', $validated['product_id'])->delete();
                return;
            }

            $priceList->items()->updateOrCreate(
                ['product_id' => $validated['product_id']],
                ['price' => $validated['price']],
            );
        });

        return back()->with('success', 'Precio actualizado.');
    }
}
