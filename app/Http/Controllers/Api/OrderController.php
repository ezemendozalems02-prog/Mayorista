<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\CreditLimitExceededException;
use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Client;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            ->when($request->filled('client_id'), fn ($q) => $q->where('client_id', $request->client_id))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return OrderResource::collection($orders);
    }

    public function store(Request $request)
    {
        $orgId = Auth::user()->organization_id;

        $validated = $request->validate([
            'client_id' => ['required', Rule::exists('clients', 'id')->where('organization_id', $orgId)],
            'items' => 'required|array|min:1',
            'items.*.product_id' => ['required', Rule::exists('products', 'id')->where('organization_id', $orgId)],
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $client = Client::findOrFail($validated['client_id']);

        $order = $this->service->create(
            client: $client,
            items: $validated['items'],
            user: $request->user(),
            notes: $validated['notes'] ?? null,
            discount: (float) ($validated['discount'] ?? 0),
        );

        return new OrderResource($order->load(['client', 'items.product']));
    }

    public function show(Order $order)
    {
        $order->load(['client', 'creator', 'items.product', 'sale']);

        return new OrderResource($order);
    }

    public function confirm(Order $order)
    {
        try {
            $this->service->confirm($order);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return new OrderResource($order->fresh()->load(['client', 'items.product']));
    }

    public function fulfill(Request $request, Order $order)
    {
        $validated = $request->validate([
            'payment_method' => 'required|string',
        ]);

        try {
            $this->service->fulfill($order, $validated['payment_method'], $request->user());
        } catch (InsufficientStockException|CreditLimitExceededException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return new OrderResource($order->fresh()->load(['client', 'items.product', 'sale']));
    }

    public function cancel(Order $order)
    {
        try {
            $this->service->cancel($order);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return new OrderResource($order->fresh());
    }
}
