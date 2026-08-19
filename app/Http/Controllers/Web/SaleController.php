<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SaleController extends Controller
{
    public function __construct(private SaleService $saleService)
    {
    }

    public function index(Request $request)
    {
        $orgId = auth()->user()->organization_id;

        $sales = Sale::where('organization_id', $orgId)
            ->with(['client', 'seller', 'items.inventoryItem', 'items.product'])
            ->when($request->search, function ($q, $search) {
                // ilike (no like): Postgres es case-sensitive por defecto, a diferencia de MySQL.
                $q->where(function ($query) use ($search) {
                    $query->where('sale_number', 'ilike', "%{$search}%")
                        ->orWhereHas('client', function ($cq) use ($search) {
                            $cq->where('full_name', 'ilike', "%{$search}%")
                                ->orWhere('phone', 'ilike', "%{$search}%");
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

        $clients = \App\Models\Client::where('organization_id', $orgId)
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

        return view('sales.create', compact('clients', 'products'));
    }

    public function store(Request $request)
    {
        $orgId = auth()->user()->organization_id;

        $validated = $request->validate([
            'client_id' => ['nullable', Rule::exists('clients', 'id')->where('organization_id', $orgId)],
            'items' => 'required|array|min:1',
            'items.*.product_id' => ['required', Rule::exists('products', 'id')->where('organization_id', $orgId)],
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'currency' => 'required|string|max:3',
            'exchange_rate' => 'nullable|numeric',
            'discount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        // Alta rapida de cliente (igual que antes: si no se eligio uno existente).
        $clientId = $validated['client_id'] ?? null;
        if (! $clientId && $request->filled('new_client_name')) {
            $client = \App\Models\Client::create([
                'organization_id' => $orgId,
                'full_name' => $request->new_client_name,
                'phone' => $request->new_client_phone,
            ]);
            $clientId = $client->id;
        }
        $validated['client_id'] = $clientId;

        try {
            $sale = $this->saleService->createSale($validated, $validated['items'], $request->user());
        } catch (InsufficientStockException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $orgId, $sale) {
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
        $sale->load(['client', 'seller', 'items.inventoryItem', 'items.product', 'payments']);
        return view('sales.show', compact('sale'));
    }

    public function downloadTicket(Sale $sale)
    {
        abort_if($sale->organization_id !== auth()->user()->organization_id, 403);

        $sale->load(['client', 'seller', 'items.inventoryItem', 'items.product', 'payments', 'organization.fiscalSetting']);

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
        $this->saleService->voidSale($sale);

        return redirect()->route('sale.index')->with('success', "Venta {$sale->sale_number} anulada con éxito.");
    }
}
