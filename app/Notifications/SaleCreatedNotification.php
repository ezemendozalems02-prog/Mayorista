<?php

namespace App\Notifications;

use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SaleCreatedNotification extends Notification
{
    use Queueable;

    protected $sale;

    public function __construct(Sale $sale)
    {
        $this->sale = $sale;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'sale_created',
            'sale_id' => $this->sale->id,
            'sale_number' => $this->sale->sale_number,
            'total' => $this->sale->total,
            'currency' => $this->sale->currency,
            'seller_name' => $this->sale->seller->full_name ?? $this->sale->seller->name,
            'title' => '¡Nueva Venta Realizada!',
            'message' => "Se ha registrado la venta {$this->sale->sale_number} por " . $this->sale->currency . " " . number_format($this->sale->total, 2),
            'url' => route('sale.show', $this->sale->id),
            'icon' => 'shopping-cart'
        ];
    }
}
