<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PriceListResource;
use App\Models\PriceList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PriceListController extends Controller
{
    public function index(Request $request)
    {
        $lists = PriceList::query()
            ->withCount(['items', 'clients'])
            ->when($request->search, fn ($q, $search) => $q->where('name', 'ilike', "%{$search}%"))
            ->orderBy('name')
            ->paginate($request->per_page ?? 15);

        return PriceListResource::collection($lists);
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

        $priceList = PriceList::create($validated);

        return new PriceListResource($priceList);
    }

    public function show(PriceList $priceList)
    {
        $priceList->loadCount(['items', 'clients'])->load('items.product');

        return new PriceListResource($priceList);
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

        $priceList->update($validated);

        return new PriceListResource($priceList);
    }

    public function destroy(PriceList $priceList)
    {
        if ($priceList->clients()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar: hay clientes con esta lista de precios asignada.',
            ], 422);
        }

        $priceList->delete();

        return response()->json(['message' => 'Lista de precios eliminada correctamente.']);
    }

    /**
     * PUT /price-lists/{priceList}/items — reemplaza todos los precios especiales
     * de la lista por el array recibido: [{product_id, price}, ...].
     */
    public function syncItems(Request $request, PriceList $priceList)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => ['required', Rule::exists('products', 'id')->where('organization_id', Auth::user()->organization_id)],
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $payload = collect($validated['items'])->mapWithKeys(fn ($item) => [
            $item['product_id'] => ['price' => $item['price']],
        ])->all();

        DB::transaction(function () use ($priceList, $payload) {
            $priceList->items()->delete();
            foreach ($payload as $productId => $data) {
                $priceList->items()->create(['product_id' => $productId, 'price' => $data['price']]);
            }
        });

        return new PriceListResource($priceList->load('items.product'));
    }
}
