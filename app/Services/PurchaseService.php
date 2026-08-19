<?php

namespace App\Services;

use App\Enums\PurchaseStatus;
use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Compras a proveedores (Fase 12). Una compra se carga en estado 'pending'
 * sin tocar el stock -- recien al recibirla (receive()) se generan los
 * movimientos de stock (StockService, Fase 6) y se actualiza el costo del
 * producto (ultimo costo de compra), igual que Ventas (Fase 11) hace con
 * PriceResolverService del lado de afuera.
 */
class PurchaseService
{
    public function __construct(private StockService $stockService)
    {
    }

    /**
     * $items: [['product_id' => int, 'quantity' => int, 'unit_cost' => float], ...]
     */
    public function create(Supplier $supplier, array $items, User $user, ?string $notes = null, float $discount = 0): Purchase
    {
        if (empty($items)) {
            throw new \InvalidArgumentException('La compra necesita al menos un producto.');
        }

        return DB::transaction(function () use ($supplier, $items, $user, $notes, $discount) {
            $subtotal = 0;
            $lines = [];

            foreach ($items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $quantity = (int) $itemData['quantity'];
                $unitCost = (float) $itemData['unit_cost'];

                if ($quantity < 1) {
                    continue;
                }

                $lineTotal = $unitCost * $quantity;
                $subtotal += $lineTotal;

                $lines[] = compact('product', 'quantity', 'unitCost', 'lineTotal');
            }

            if (empty($lines)) {
                throw new \InvalidArgumentException('La compra necesita al menos un producto con cantidad válida.');
            }

            $total = $subtotal - $discount;

            $purchase = Purchase::create([
                'organization_id' => $user->organization_id,
                'supplier_id' => $supplier->id,
                'status' => PurchaseStatus::PENDING,
                'subtotal' => number_format($subtotal, 2, '.', ''),
                'discount' => number_format($discount, 2, '.', ''),
                'total' => number_format($total, 2, '.', ''),
                'notes' => $notes,
                'created_by' => $user->id,
            ]);

            foreach ($lines as $line) {
                $purchase->items()->create([
                    'product_id' => $line['product']->id,
                    'quantity' => $line['quantity'],
                    'unit_cost' => number_format($line['unitCost'], 2, '.', ''),
                    'line_total' => number_format($line['lineTotal'], 2, '.', ''),
                ]);
            }

            return $purchase;
        });
    }

    /**
     * Confirma que la mercaderia llego: por cada linea, suma stock (type
     * PURCHASE) y actualiza el costo del producto al ultimo costo de compra.
     */
    public function receive(Purchase $purchase): Purchase
    {
        if ($purchase->status !== PurchaseStatus::PENDING) {
            throw new \RuntimeException('Esta compra ya fue recibida o cancelada.');
        }

        return DB::transaction(function () use ($purchase) {
            foreach ($purchase->items()->with('product')->get() as $item) {
                $this->stockService->recordMovement(
                    product: $item->product,
                    quantityDelta: $item->quantity,
                    type: StockMovementType::PURCHASE,
                    unitCost: (float) $item->unit_cost,
                    referenceType: 'purchase',
                    referenceId: $purchase->id,
                );

                // Ultimo costo de compra pasa a ser el costo del producto.
                $item->product->update(['cost' => $item->unit_cost]);
            }

            $purchase->update([
                'status' => PurchaseStatus::RECEIVED,
                'received_at' => now(),
            ]);

            return $purchase;
        });
    }

    public function cancel(Purchase $purchase): Purchase
    {
        if ($purchase->status !== PurchaseStatus::PENDING) {
            throw new \RuntimeException('Esta compra ya fue recibida o cancelada.');
        }

        $purchase->update(['status' => PurchaseStatus::CANCELLED]);

        return $purchase;
    }
}
