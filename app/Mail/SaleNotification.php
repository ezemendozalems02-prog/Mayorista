<?php

namespace App\Mail;

use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SaleNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $sale;

    public function __construct(Sale $sale)
    {
        $this->sale = $sale->load(['items', 'client']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva Venta Registrada: ' . $this->sale->sale_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sale-notification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
