<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Models\User;
use App\Notifications\ProductLowStockNotification;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Unico punto de entrada para modificar stock. El principio de diseno es
 * "nunca escribir stock directamente": todo cambio se registra como un
 * StockMovement (ledger append-only, ver esa clase) y esta clase es la unica
 * que actualiza el cache derivado en ProductStock, dentro de la misma
 * transaccion y con lockForUpdate para evitar carreras entre movimientos
 * concurrentes del mismo producto (ej. dos ventas simultaneas).
 */
class StockService
{
    /**
     * Registra un movimiento de stock y actualiza el cache. $quantityDelta
     * es con signo: positivo = entrada, negativo = salida. Nunca 0.
     */
    public function recordMovement(
        Product $product,
        int $quantityDelta,
        StockMovementType $type,
        ?string $notes = null,
        ?float $unitCost = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $userId = null,
    ): StockMovement {
        if ($quantityDelta === 0) {
            throw new \InvalidArgumentException('quantityDelta no puede ser 0.');
        }

        return DB::transaction(function () use ($product, $quantityDelta, $type, $notes, $unitCost, $referenceType, $referenceId, $userId) {
            $stock = $this->lockOrCreateStock($product);

            $movement = StockMovement::create([
                'organization_id' => $product->organization_id,
                'product_id' => $product->id,
                'type' => $type,
                'quantity_delta' => $quantityDelta,
                'unit_cost' => $unitCost,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'user_id' => $userId ?? Auth::id(),
            ]);

            $before = $stock->quantity;
            $stock->quantity += $quantityDelta;
            $stock->save();

            $this->notifyIfLowStockCrossed($product, $before, $stock->quantity);

            return $movement;
        });
    }

    /**
     * Alerta de stock bajo (Fase 20): se dispara SOLO en el movimiento que
     * hace que el stock pase de estar por encima de min_stock a estar en o
     * por debajo -- no en cada movimiento mientras ya esta bajo, para no
     * spamear a los dueños. Si min_stock no esta configurado (0 o null),
     * no hay umbral y no se notifica nunca para ese producto.
     */
    private function notifyIfLowStockCrossed(Product $product, int $before, int $after): void
    {
        $threshold = (int) ($product->min_stock ?? 0);

        if ($threshold <= 0 || $before <= $threshold || $after > $threshold) {
            return;
        }

        $owners = User::where('organization_id', $product->organization_id)
            ->where('role', \App\Enums\UserRole::OWNER)
            ->get();

        foreach ($owners as $owner) {
            $owner->notify(new ProductLowStockNotification($product, $after));
        }
    }

    public function currentStock(Product $product): int
    {
        return ProductStock::where('product_id', $product->id)->value('quantity') ?? 0;
    }

    /**
     * Recalcula el cache desde cero sumando todo el ledger. Herramienta de
     * verificacion/reparacion; en operacion normal el cache se mantiene solo
     * via recordMovement().
     */
    public function recalculate(Product $product): int
    {
        return DB::transaction(function () use ($product) {
            $stock = $this->lockOrCreateStock($product);

            $total = StockMovement::where('product_id', $product->id)->sum('quantity_delta');

            $stock->quantity = (int) $total;
            $stock->save();

            return $stock->quantity;
        });
    }

    /**
     * Devuelve (creando si hace falta) la fila de ProductStock ya bloqueada
     * con SELECT ... FOR UPDATE. El create usa un savepoint propio: si otro
     * proceso la crea en paralelo, el unique(product_id) lo rechaza y
     * simplemente recuperamos la fila que gano la carrera, sin abortar la
     * transaccion externa (ver Fase 5 para el mismo patron con barcode).
     */
    private function lockOrCreateStock(Product $product): ProductStock
    {
        $stock = ProductStock::where('product_id', $product->id)->lockForUpdate()->first();

        if ($stock) {
            return $stock;
        }

        try {
            DB::transaction(function () use ($product, &$stock) {
                $stock = ProductStock::create([
                    'organization_id' => $product->organization_id,
                    'product_id' => $product->id,
                    'quantity' => 0,
                ]);
            });
        } catch (QueryException $e) {
            // Carrera: otro proceso ya la creo. La recuperamos y la bloqueamos.
            $stock = null;
        }

        return $stock ?? ProductStock::where('product_id', $product->id)->lockForUpdate()->firstOrFail();
    }
}
