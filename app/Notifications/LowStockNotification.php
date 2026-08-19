<?php

namespace App\Notifications;

use App\Models\SparePart;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    protected $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'low_stock',
            'item_id' => $this->item->id,
            'item_name' => $this->item->name,
            'current_stock' => $this->item->stock,
            'title' => '¡Stock Crítico!',
            'message' => "El producto {$this->item->name} (SKU: {$this->item->sku}) tiene solo {$this->item->stock} unidades disponibles.",
            'url' => route('inventory.index'),
            'icon' => 'package'
        ];
    }
}
