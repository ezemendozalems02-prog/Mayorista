<?php

namespace App\Services;

use App\Enums\AccountMovementType;
use App\Enums\StockMovementType;
use App\Exceptions\CreditLimitExceededException;
use App\Exceptions\InsufficientStockException;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Construye una venta a partir de productos del catalogo (Fase 5), resolviendo
 * el precio con PriceResolverService (Fase 10) y descontando stock con
 * StockService (Fase 6). Reemplaza el flujo viejo de Vortex atado a
 * InventoryItem (celulares), que queda desactivado pero no borrado.
 */
class SaleService
{
    public function __construct(
        private StockService $stockService,
        private PriceResolverService $priceResolver,
        private AccountService $accountService,
    ) {
    }

    /**
     * $items: [['product_id' => int, 'quantity' => int, 'unit_price' => ?float], ...]
     * Si unit_price no viene, se resuelve con PriceResolverService segun el cliente.
     * $attributes['payment_method'] === 'account': carga el total a la cuenta
     * corriente del cliente (Fase 13) en la misma transaccion -- si supera su
     * limite de credito, se rechaza la venta entera (nada se crea).
     *
     * @throws InsufficientStockException
     * @throws CreditLimitExceededException
     */
    public function createSale(array $attributes, array $items, User $seller): Sale
    {
        if (empty($items)) {
            throw new \InvalidArgumentException('La venta necesita al menos un producto.');
        }

        $onAccount = ($attributes['payment_method'] ?? null) === 'account';

        return DB::transaction(function () use ($attributes, $items, $seller, $onAccount) {
            $client = ! empty($attributes['client_id']) ? Client::find($attributes['client_id']) : null;

            if ($onAccount && ! $client) {
                throw new \InvalidArgumentException('Una venta a cuenta corriente necesita un cliente.');
            }

            $lines = $this->resolveLines($items, $client);

            $subtotal = 0;
            $costTotal = 0;
            foreach ($lines as $line) {
                $subtotal += $line['line_total'];
                $costTotal += $line['unit_cost'] * $line['quantity'];
            }

            $discount = (float) ($attributes['discount'] ?? 0);
            $total = $subtotal - $discount;

            $sale = Sale::create([
                'organization_id' => $seller->organization_id,
                'client_id' => $client?->id,
                'seller_id' => $seller->id,
                'sale_number' => 'S-' . strtoupper(Str::random(8)),
                'status' => 'completed',
                'currency' => $attributes['currency'] ?? 'ARS',
                'exchange_rate' => $attributes['exchange_rate'] ?? null,
                // number_format (no el float crudo): brick/math, usado por el cast
                // decimal:2, deprecó pasarle floats directo en PHP 8.4.
                'subtotal' => number_format($subtotal, 2, '.', ''),
                'discount' => number_format($discount, 2, '.', ''),
                'total' => number_format($total, 2, '.', ''),
                'cost_total' => number_format($costTotal, 2, '.', ''),
                'profit_total' => number_format($total - $costTotal, 2, '.', ''),
                'notes' => $attributes['notes'] ?? null,
                'sold_at' => now(),
            ]);

            foreach ($lines as $line) {
                $sale->items()->create([
                    'product_id' => $line['product']->id,
                    'item_name' => $line['product']->name,
                    'unit_cost' => number_format($line['unit_cost'], 2, '.', ''),
                    'unit_price' => number_format($line['unit_price'], 2, '.', ''),
                    'quantity' => $line['quantity'],
                    'line_total' => number_format($line['line_total'], 2, '.', ''),
                ]);

                $this->stockService->recordMovement(
                    product: $line['product'],
                    quantityDelta: -$line['quantity'],
                    type: StockMovementType::SALE,
                    referenceType: 'sale',
                    referenceId: $sale->id,
                );
            }

            if ($onAccount) {
                $this->accountService->recordMovement(
                    client: $client,
                    amount: (float) $sale->total,
                    type: AccountMovementType::SALE,
                    notes: "Venta {$sale->sale_number}",
                    referenceType: 'sale',
                    referenceId: $sale->id,
                );
            }

            return $sale;
        });
    }

    /**
     * Anula una venta: revierte el stock de cada linea (RETURN, con nota),
     * revierte el cargo a cuenta corriente si la venta fue a cuenta (Fase 13,
     * via nota de credito -- no se puede simplemente borrar el movimiento
     * porque el ledger es append-only), y borra la venta y sus pagos. Los
     * items de InventoryItem (flujo viejo, desactivado) se siguen
     * restaurando por compatibilidad.
     */
    public function voidSale(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            foreach ($sale->items as $item) {
                if ($item->product_id) {
                    $this->stockService->recordMovement(
                        product: $item->product,
                        quantityDelta: $item->quantity,
                        type: StockMovementType::RETURN,
                        notes: "Venta {$sale->sale_number} anulada",
                        referenceType: 'sale',
                        referenceId: $sale->id,
                    );
                }

                if ($item->inventoryItem) {
                    $item->inventoryItem->update(['status' => \App\Enums\InventoryStatus::IN_STOCK]);
                }
            }

            $wasOnAccount = \App\Models\AccountMovement::where('reference_type', 'sale')
                ->where('reference_id', $sale->id)
                ->where('type', AccountMovementType::SALE)
                ->exists();

            if ($wasOnAccount && $sale->client) {
                $this->accountService->recordMovement(
                    client: $sale->client,
                    amount: -(float) $sale->total,
                    type: AccountMovementType::CREDIT_NOTE,
                    notes: "Venta {$sale->sale_number} anulada",
                    referenceType: 'sale',
                    referenceId: $sale->id,
                );
            }

            $sale->payments()->delete();
            $sale->delete();
        });
    }

    /**
     * Valida stock disponible para todas las lineas antes de tocar nada, y
     * resuelve precio/costo por linea.
     *
     * @throws InsufficientStockException
     */
    private function resolveLines(array $items, ?Client $client): array
    {
        $shortages = [];
        $lines = [];

        foreach ($items as $itemData) {
            $product = Product::findOrFail($itemData['product_id']);
            $quantity = (int) $itemData['quantity'];

            if ($quantity < 1) {
                continue;
            }

            $available = $product->current_stock;
            if ($available < $quantity) {
                $shortages[] = "{$product->name} (disponible: {$available}, pedido: {$quantity})";
            }

            $unitPrice = isset($itemData['unit_price']) && $itemData['unit_price'] !== null && $itemData['unit_price'] !== ''
                ? (float) $itemData['unit_price']
                : $this->priceResolver->priceFor($product, $client);

            $unitCost = (float) ($product->cost ?? 0);

            $lines[] = [
                'product' => $product,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'unit_cost' => $unitCost,
                'line_total' => $unitPrice * $quantity,
            ];
        }

        if (! empty($shortages)) {
            throw new InsufficientStockException('Stock insuficiente: ' . implode('; ', $shortages));
        }

        if (empty($lines)) {
            throw new \InvalidArgumentException('La venta necesita al menos un producto con cantidad válida.');
        }

        return $lines;
    }
}
