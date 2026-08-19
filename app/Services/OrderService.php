<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Pedidos (Fase 17): un compromiso de venta que se carga hoy y se factura
 * despues -- tipico del mayorista ("el cliente encarga, se prepara, se
 * entrega y recien ahi se cobra"). A diferencia de una Sale (Fase 11), crear
 * o confirmar un pedido NO toca stock ni cuenta corriente. Solo al cumplirlo
 * (fulfill) se delega en SaleService::createSale() -- ahi, y solo ahi, se
 * valida stock de verdad y se descuenta. Esta clase nunca duplica esa
 * logica: la reusa.
 */
class OrderService
{
    public function __construct(
        private PriceResolverService $priceResolver,
        private SaleService $saleService,
    ) {
    }

    /**
     * $items: [['product_id' => int, 'quantity' => int, 'unit_price' => ?float], ...]
     * Si unit_price no viene, se resuelve con PriceResolverService segun el
     * cliente -- y ese precio queda "congelado" en el pedido (no se
     * recalcula despues, aunque cambien las listas de precios).
     */
    public function create(Client $client, array $items, User $user, ?string $notes = null, float $discount = 0): Order
    {
        if (empty($items)) {
            throw new \InvalidArgumentException('El pedido necesita al menos un producto.');
        }

        return DB::transaction(function () use ($client, $items, $user, $notes, $discount) {
            $lines = $this->resolveLines($items, $client);

            $subtotal = 0;
            foreach ($lines as $line) {
                $subtotal += $line['line_total'];
            }
            $total = $subtotal - $discount;

            $order = Order::create([
                'organization_id' => $client->organization_id,
                'client_id' => $client->id,
                'status' => OrderStatus::DRAFT,
                'subtotal' => number_format($subtotal, 2, '.', ''),
                'discount' => number_format($discount, 2, '.', ''),
                'total' => number_format($total, 2, '.', ''),
                'notes' => $notes,
                'created_by' => $user->id,
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'item_name' => $line['product']->name,
                    'unit_price' => number_format($line['unit_price'], 2, '.', ''),
                    'quantity' => $line['quantity'],
                    'line_total' => number_format($line['line_total'], 2, '.', ''),
                ]);
            }

            return $order;
        });
    }

    /**
     * DRAFT -> CONFIRMED. Solo un cambio de estado (el cliente confirmo el
     * pedido); sigue sin tocar stock ni cuenta corriente.
     */
    public function confirm(Order $order): Order
    {
        if ($order->status !== OrderStatus::DRAFT) {
            throw new \RuntimeException('Solo un pedido en borrador puede confirmarse.');
        }

        $order->update(['status' => OrderStatus::CONFIRMED]);

        return $order->fresh();
    }

    /**
     * Cumple el pedido: lo convierte en una Sale real (Fase 11) con los
     * precios que quedaron congelados al cargarlo. Ahi es donde SaleService
     * valida stock disponible y, si corresponde, carga la cuenta corriente
     * (Fase 13) -- todo en una unica transaccion. Si falta stock, la
     * excepcion sube tal cual y el pedido queda como estaba (nada a medias).
     *
     * @throws \App\Exceptions\InsufficientStockException
     * @throws \App\Exceptions\CreditLimitExceededException
     */
    public function fulfill(Order $order, string $paymentMethod, User $user): Order
    {
        if (! in_array($order->status, [OrderStatus::DRAFT, OrderStatus::CONFIRMED], true)) {
            throw new \RuntimeException('Este pedido ya fue facturado o cancelado.');
        }

        return DB::transaction(function () use ($order, $paymentMethod, $user) {
            $items = $order->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
            ])->all();

            $sale = $this->saleService->createSale(
                [
                    'client_id' => $order->client_id,
                    'payment_method' => $paymentMethod,
                    'discount' => (float) $order->discount,
                    'notes' => "Pedido {$order->code}",
                ],
                $items,
                $user,
            );

            $order->update([
                'status' => OrderStatus::FULFILLED,
                'sale_id' => $sale->id,
                'fulfilled_at' => now(),
            ]);

            return $order->fresh();
        });
    }

    /**
     * Cancela un pedido no facturado. Como crear/confirmar nunca tocaron
     * stock, cancelar tampoco tiene nada que revertir.
     */
    public function cancel(Order $order): Order
    {
        if ($order->status === OrderStatus::FULFILLED) {
            throw new \RuntimeException('No se puede cancelar un pedido que ya fue facturado.');
        }
        if ($order->status === OrderStatus::CANCELLED) {
            throw new \RuntimeException('Este pedido ya está cancelado.');
        }

        $order->update(['status' => OrderStatus::CANCELLED]);

        return $order->fresh();
    }

    /**
     * Resuelve precio/nombre por linea. A proposito NO valida stock (a
     * diferencia de SaleService::resolveLines): un pedido puede cargarse
     * aunque hoy falte stock -- puede llegar con la proxima compra. La
     * validacion real ocurre en fulfill(), delegada a SaleService.
     */
    private function resolveLines(array $items, Client $client): array
    {
        $lines = [];

        foreach ($items as $itemData) {
            $product = Product::findOrFail($itemData['product_id']);
            $quantity = (int) $itemData['quantity'];

            if ($quantity < 1) {
                continue;
            }

            $unitPrice = isset($itemData['unit_price']) && $itemData['unit_price'] !== null && $itemData['unit_price'] !== ''
                ? (float) $itemData['unit_price']
                : $this->priceResolver->priceFor($product, $client);

            $lines[] = [
                'product' => $product,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $unitPrice * $quantity,
            ];
        }

        if (empty($lines)) {
            throw new \InvalidArgumentException('El pedido necesita al menos un producto con cantidad válida.');
        }

        return $lines;
    }
}
