<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $orgId = auth()->user()->organization_id;

        $sales = Sale::where('organization_id', $orgId)
            ->with(['client', 'seller', 'items.inventoryItem'])
            ->when($request->search, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('sale_number', 'like', "%{$search}%")
                        ->orWhereHas('client', function ($cq) use ($search) {
                            $cq->where('full_name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->date_range, function ($q, $range) {
                switch ($range) {
                    case 'today':
                        $q->whereDate('sold_at', now());
                        break;
                    case 'week':
                        $q->whereBetween('sold_at', [now()->startOfWeek(), now()->endOfWeek()]);
                        break;
                    case 'month':
                        $q->whereMonth('sold_at', now()->month)->whereYear('sold_at', now()->year);
                        break;
                }
            })
            ->when($request->from && $request->to, function ($q) use ($request) {
                $q->whereBetween('sold_at', [$request->from, $request->to]);
            })
            ->latest('sold_at')
            ->paginate(15)
            ->withQueryString();

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $orgId = auth()->user()->organization_id;
        $clients = \App\Models\Client::where('organization_id', $orgId)->orderBy('full_name')->get();
        $inventoryItems = \App\Models\InventoryItem::where('organization_id', $orgId)
            ->where('status', \App\Enums\InventoryStatus::IN_STOCK)
            ->orderBy('model')
            ->get();

        return view('sales.create', compact('clients', 'inventoryItems'));
    }

    public function store(Request $request)
    {
        $orgId = auth()->user()->organization_id;

        $validated = $request->validate([
            'client_id' => ['nullable', Rule::exists('clients', 'id')->where('organization_id', $orgId)],
            'items' => 'required|array|min:1',
            'items.*.item_ids' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'currency' => 'required|string|max:3',
            'exchange_rate' => 'nullable|numeric',
            'discount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $validated, $orgId) {
            // Handle Quick Client Creation
            $clientId = $validated['client_id'] ?? null;
            if (!$clientId && $request->filled('new_client_name')) {
                $client = \App\Models\Client::create([
                    'organization_id' => $orgId,
                    'full_name' => $request->new_client_name,
                    'phone' => $request->new_client_phone,
                ]);
                $clientId = $client->id;
            }

            $itemsData = $request->input('items');
            $subtotal = 0;
            $costTotal = 0;

            $sale = \App\Models\Sale::create([
                'organization_id' => $orgId,
                'client_id' => $clientId,
                'seller_id' => auth()->id(),
                'sale_number' => 'S-' . strtoupper(\Illuminate\Support\Str::random(8)),
                'status' => 'completed',
                'currency' => $validated['currency'],
                'exchange_rate' => $validated['exchange_rate'] ?? 1,
                'discount' => $validated['discount'] ?? 0,
                'subtotal' => 0,
                'total' => 0,
                'cost_total' => 0,
                'profit_total' => 0,
                'sold_at' => now(),
            ]);

            foreach ($itemsData as $itemInput) {
                $allowedIds = json_decode($itemInput['item_ids'], true);
                if (!is_array($allowedIds))
                    continue;

                $qty = $itemInput['quantity'] ?? 1;

                // Fetch exactly $qty available items from the group
                $invItems = \App\Models\InventoryItem::whereIn('id', $allowedIds)
                    ->where('status', \App\Enums\InventoryStatus::IN_STOCK)
                    ->limit($qty)
                    ->get();

                if ($invItems->count() < $qty) {
                    throw new \Exception("No hay suficiente stock para cubrir la cantidad solicitada.");
                }

                foreach ($invItems as $invItem) {
                    /** @var \App\Models\InventoryItem $invItem */
                    \App\Models\SaleItem::create([
                        'sale_id' => $sale->id,
                        'inventory_item_id' => $invItem->id,
                        'item_name' => "{$invItem->brand} {$invItem->model} ({$invItem->storage})",
                        'unit_cost' => $invItem->purchase_price,
                        'unit_price' => $invItem->sale_price,
                        'quantity' => 1,
                        'line_total' => $invItem->sale_price,
                    ]);

                    $subtotal += $invItem->sale_price;
                    $costTotal += $invItem->purchase_price;

                    $invItem->update(['status' => \App\Enums\InventoryStatus::SOLD]);
                }
            }

            $total = $subtotal - ($validated['discount'] ?? 0);
            $profit = $total - $costTotal;

            $sale->update([
                'subtotal' => $subtotal,
                'total' => $total,
                'cost_total' => $costTotal,
                'profit_total' => $profit,
            ]);

            // Record Payment
            \App\Models\Payment::create([
                'organization_id' => $orgId,
                'sale_id' => $sale->id,
                'client_id' => $sale->client_id,
                'type' => 'income',
                'method' => $validated['payment_method'],
                'currency' => $sale->currency,
                'amount' => $sale->total,
                'paid_at' => now(),
            ]);

            // Record Trade-In if applicable
            if ($request->boolean('has_trade_in')) {
                \App\Models\TradeIn::create([
                    'organization_id' => $orgId,
                    'sale_id' => $sale->id,
                    'client_id' => $sale->client_id,
                    'brand' => 'Apple', // Default or parse from model
                    'model' => $request->trade_in_model,
                    'storage' => $request->trade_in_storage,
                    'imei' => $request->trade_in_imei,
                    'battery_health' => $request->trade_in_battery_health,
                    'appraised_value' => $request->trade_in_value,
                    'currency' => $sale->currency,
                    'notes' => $request->trade_in_notes,
                    'condition' => 'Used', // Default
                ]);
            }

            // Notifications
            try {
                $organization = \App\Models\Organization::find($orgId);
                if ($organization && $organization->notifications_email_enabled) {
                    $recipient = $organization->notifications_email_alias ?: $organization->email;
                    if ($recipient) {
                        \Illuminate\Support\Facades\Mail::to($recipient)->send(new \App\Mail\SaleNotification($sale));
                        \App\Services\MonitoringService::log('sales', "Email notification sent to {$recipient} for sale {$sale->sale_number}", 'info');
                    }
                }

                // In-app Notification for Owners
                $owners = \App\Models\User::where('organization_id', $orgId)
                    ->where('role', \App\Enums\UserRole::OWNER)
                    ->get();
                
                foreach ($owners as $owner) {
                    $owner->notify(new \App\Notifications\SaleCreatedNotification($sale));
                }

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send sale notification: " . $e->getMessage());
            }

            return redirect()->route('sale.index')->with('success', "Venta {$sale->sale_number} realizada con éxito.");
        });
    }

    public function show(Sale $sale)
    {
        $sale->load(['client', 'seller', 'items.inventoryItem', 'payments']);
        return view('sales.show', compact('sale'));
    }

    public function downloadTicket(Sale $sale)
    {
        abort_if($sale->organization_id !== auth()->user()->organization_id, 403);

        $sale->load(['client', 'seller', 'items.inventoryItem', 'payments', 'organization.fiscalSetting']);

        $pdf = Pdf::loadView('pdf.sale-ticket', [
            'sale'          => $sale,
            'organization'  => $sale->organization,
            'fiscalSetting' => $sale->organization?->fiscalSetting,
        ])->setPaper([0, 0, 595, 841], 'portrait');

        $filename = 'ticket-' . $sale->sale_number . '.pdf';

        return $pdf->download($filename);
    }

    public function destroy(Sale $sale)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($sale) {
            // Restore inventory items status
            foreach ($sale->items as $item) {
                if ($item->inventoryItem) {
                    $item->inventoryItem->update(['status' => \App\Enums\InventoryStatus::IN_STOCK]);
                }
            }

            // Delete associated payments
            $sale->payments()->delete();

            // Delete the sale (SoftDelete)
            $sale->delete();

            return redirect()->route('sale.index')->with('success', "Venta {$sale->sale_number} anulada con éxito.");
        });
    }
}
