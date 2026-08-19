<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\CreditLimitExceededException;
use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function __construct(private OrderService $service)
    {
    }

    public function index(Request $request)
    {
        $orders = Order::query()
            ->with('client')
            ->withCount('items')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        $orgId = auth()->user()->organization_id;

        $clients = Client::where('organization_id', $orgId)
            ->select('id', 'full_name', 'business_name', 'client_type', 'discount_percentage')
            ->orderBy('full_name')
            ->get();

        $products = Product::where('status', \App\Enums\ProductStatus::ACTIVE)
            ->with('stock')
            ->orderBy('name')
            ->get()
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'internal_code' => $p->internal_code,
                'barcode' => $p->barcode,
                'retail_price' => (float) ($p->retail_price ?? 0),
                'wholesale_price' => (float) ($p->wholesale_price ?? $p->retail_price ?? 0),
                'stock' => $p->current_stock,
            ]);

        return view('orders.create', compact('clients', 'products'));
    }

    public function store(Request $request)
    {
        $orgId = auth()->user()->organization_id;

        $validated = $request->validate([
            'client_id' => ['required', Rule::exists('clients', 'id')->where('organization_id', $orgId)],
            'items' => 'required|array|min:1',
            'items.*.product_id' => ['required', Rule::exists('products', 'id')->where('organization_id', $orgId)],
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // client_id es obligatorio (a diferencia de Sale): un pedido siempre
        // se le hace a alguien. El alta rápida de cliente (igual que en
        // Ventas) ya deja el id cargado en el campo antes de enviar el form.
        $client = Client::findOrFail($validated['client_id']);

        $order = $this->service->create(
            client: $client,
            items: $validated['items'],
            user: $request->user(),
            notes: $validated['notes'] ?? null,
            discount: (float) ($validated['discount'] ?? 0),
        );

        return redirect()->route('order.show', $order)->with('success', "Pedido {$order->code} creado. Confirmalo cuando el cliente lo apruebe.");
    }

    public function show(Order $order)
    {
        $order->load(['client', 'creator', 'items.product', 'sale']);

        return view('orders.show', compact('order'));
    }

    public function confirm(Order $order)
    {
        try {
            $this->service->confirm($order);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('order.show', $order)->with('success', "Pedido {$order->code} confirmado.");
    }

    public function fulfill(Request $request, Order $order)
    {
        $validated = $request->validate([
            'payment_method' => 'required|string',
        ]);

        try {
            $this->service->fulfill($order, $validated['payment_method'], $request->user());
        } catch (InsufficientStockException|CreditLimitExceededException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('order.show', $order)->with('success', "Pedido {$order->code} facturado correctamente.");
    }

    public function cancel(Order $order)
    {
        try {
            $this->service->cancel($order);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('order.index')->with('success', "Pedido {$order->code} cancelado.");
    }
}
