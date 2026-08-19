<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\CreditLimitExceededException;
use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SaleController extends Controller
{
    public function __construct(private SaleService $saleService)
    {
    }

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
            'client_id' => ['nullable', Rule::exists('clients', 'id')->where('organization_id', Auth::user()->organization_id)],
            'items' => 'required|array|min:1',
            'items.*.product_id' => ['required', Rule::exists('products', 'id')->where('organization_id', Auth::user()->organization_id)],
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'exchange_rate' => 'nullable|numeric',
            'discount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $sale = $this->saleService->createSale($validated, $validated['items'], $request->user());
        } catch (InsufficientStockException|CreditLimitExceededException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($sale->load(['items.product', 'client']), 201);
    }

    public function show(Sale $sale)
    {
        return response()->json($sale->load(['client', 'items.product', 'payments']));
    }

    public function destroy(Sale $sale)
    {
        $this->saleService->voidSale($sale);

        return response()->json(['message' => 'Venta anulada correctamente.']);
    }
}
