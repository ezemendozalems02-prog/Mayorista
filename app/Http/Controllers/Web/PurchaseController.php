<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
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
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('business_name')->get();

        // Productos con el costo especifico por proveedor (si esta cargado en
        // product_supplier, Fase 5) para pre-completar el formulario.
        $products = Product::with('suppliers')
            ->orderBy('name')
            ->get()
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'internal_code' => $p->internal_code,
                'cost' => (float) ($p->cost ?? 0),
                'supplier_costs' => $p->suppliers->mapWithKeys(fn ($s) => [$s->id => (float) ($s->pivot->cost ?? $p->cost ?? 0)]),
            ]);

        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $orgId = auth()->user()->organization_id;

        $validated = $request->validate([
            'supplier_id' => ['required', Rule::exists('suppliers', 'id')->where('organization_id', $orgId)],
            'items' => 'required|array|min:1',
            'items.*.product_id' => ['required', Rule::exists('products', 'id')->where('organization_id', $orgId)],
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

        return redirect()->route('purchase.show', $purchase)->with('success', "Compra {$purchase->code} cargada. Confirmá la recepción cuando llegue la mercadería.");
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'creator', 'items.product']);

        return view('purchases.show', compact('purchase'));
    }

    public function receive(Purchase $purchase)
    {
        try {
            $this->service->receive($purchase);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('purchase.show', $purchase)->with('success', "Compra {$purchase->code} recibida: stock y costos actualizados.");
    }

    public function cancel(Purchase $purchase)
    {
        try {
            $this->service->cancel($purchase);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('purchase.index')->with('success', "Compra {$purchase->code} cancelada.");
    }
}
