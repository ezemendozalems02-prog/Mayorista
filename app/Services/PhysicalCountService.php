<?php

namespace App\Services;

use App\Enums\PhysicalCountStatus;
use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\PhysicalCount;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Orquesta un conteo fisico de inventario (Fase 8): saca una foto del stock
 * del sistema, deja cargar cantidades contadas, y al finalizar aplica las
 * diferencias como StockMovement(type: PHYSICAL_COUNT) via StockService --
 * nunca toca product_stocks directamente, respetando el mismo principio de
 * Fase 6.
 */
class PhysicalCountService
{
    public function __construct(private StockService $stockService)
    {
    }

    /**
     * Arranca un conteo: crea la cabecera y saca una foto (expected_quantity)
     * del stock actual de cada producto activo, opcionalmente filtrado por
     * categoria.
     */
    public function start(?Category $category, User $user, ?string $notes = null): PhysicalCount
    {
        return DB::transaction(function () use ($category, $user, $notes) {
            $count = PhysicalCount::create([
                'organization_id' => $user->organization_id,
                'category_id' => $category?->id,
                'status' => PhysicalCountStatus::OPEN,
                'notes' => $notes,
                'created_by' => $user->id,
            ]);

            $products = Product::query()
                ->with('stock')
                ->where('status', \App\Enums\ProductStatus::ACTIVE)
                ->when($category, fn ($q) => $q->where('category_id', $category->id))
                ->get();

            $rows = $products->map(fn (Product $product) => [
                'physical_count_id' => $count->id,
                'product_id' => $product->id,
                'expected_quantity' => $product->stock?->quantity ?? 0,
                'counted_quantity' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ])->all();

            if (! empty($rows)) {
                DB::table('physical_count_items')->insert($rows);
            }

            return $count;
        });
    }

    /**
     * Guarda (o actualiza) las cantidades contadas. $counts: [item_id => cantidad|null].
     * Solo permitido mientras el conteo esta OPEN.
     */
    public function saveCounts(PhysicalCount $count, array $counts): void
    {
        if ($count->status !== PhysicalCountStatus::OPEN) {
            throw new \RuntimeException('Este conteo ya fue finalizado o cancelado; no se pueden cargar mas cantidades.');
        }

        DB::transaction(function () use ($count, $counts) {
            foreach ($counts as $itemId => $quantity) {
                $item = $count->items()->whereKey($itemId)->first();

                if (! $item) {
                    continue; // item de otro conteo, ignorado
                }

                $item->update([
                    'counted_quantity' => $quantity === null || $quantity === '' ? null : (int) $quantity,
                    'counted_at' => $quantity === null || $quantity === '' ? null : now(),
                ]);
            }
        });
    }

    /**
     * Cierra el conteo: por cada item contado cuya cantidad difiere de la
     * esperada, registra un StockMovement de tipo PHYSICAL_COUNT con el delta
     * correspondiente. Items sin contar se ignoran (no se asume 0).
     *
     * @return array{count: PhysicalCount, adjustments: array}
     */
    public function finalize(PhysicalCount $count): array
    {
        if ($count->status !== PhysicalCountStatus::OPEN) {
            throw new \RuntimeException('Este conteo ya fue finalizado o cancelado.');
        }

        return DB::transaction(function () use ($count) {
            $adjustments = [];

            $items = $count->items()->with('product')->whereNotNull('counted_quantity')->get();

            foreach ($items as $item) {
                $delta = $item->counted_quantity - $item->expected_quantity;

                if ($delta === 0) {
                    continue;
                }

                $this->stockService->recordMovement(
                    product: $item->product,
                    quantityDelta: $delta,
                    type: StockMovementType::PHYSICAL_COUNT,
                    notes: "Conteo fisico {$count->code}",
                    referenceType: 'physical_count',
                    referenceId: $count->id,
                );

                $adjustments[] = [
                    'product' => $item->product->name,
                    'expected' => $item->expected_quantity,
                    'counted' => $item->counted_quantity,
                    'delta' => $delta,
                ];
            }

            $count->update([
                'status' => PhysicalCountStatus::COMPLETED,
                'completed_at' => now(),
            ]);

            return ['count' => $count, 'adjustments' => $adjustments];
        });
    }

    public function cancel(PhysicalCount $count): void
    {
        if ($count->status !== PhysicalCountStatus::OPEN) {
            throw new \RuntimeException('Este conteo ya fue finalizado o cancelado.');
        }

        $count->update(['status' => PhysicalCountStatus::CANCELLED]);
    }
}
