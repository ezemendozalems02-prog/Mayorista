<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Alerta de stock bajo de catalogo (Fase 20). Nombre deliberadamente
 * distinto de App\Notifications\LowStockNotification: esa clase ya
 * existia, heredada de Vortex, para SparePart (repuestos de celular,
 * modulo desactivado pero no borrado -- ver SparePartController) y sigue
 * viva con su propia firma; reusar el nombre habria roto ese call site.
 *
 * Se dispara UNA vez por cruce de umbral -- ver
 * StockService::notifyIfLowStockCrossed(), que solo la envia cuando el
 * stock pasa de estar por ENCIMA de min_stock a estar en o por DEBAJO,
 * nunca en cada movimiento mientras ya esta bajo (evita spam).
 */
class ProductLowStockNotification extends Notification
{
    use Queueable;

    protected Product $product;
    protected int $currentStock;

    public function __construct(Product $product, int $currentStock)
    {
        $this->product = $product;
        $this->currentStock = $currentStock;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'product_low_stock',
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'current_stock' => $this->currentStock,
            'min_stock' => $this->product->min_stock,
            'title' => 'Stock bajo',
            'message' => "{$this->product->name} quedó en {$this->currentStock} unidades (mínimo configurado: {$this->product->min_stock}).",
            'url' => route('stock.index', ['low_stock' => 1]),
            'icon' => 'triangle-alert',
        ];
    }
}
