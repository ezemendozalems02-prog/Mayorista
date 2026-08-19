<?php

namespace App\Mail;

use App\Models\Organization;
use App\Models\SubscriptionPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionPaidNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $organization;
    public $plan;
    public $amount;

    public function __construct(Organization $organization, SubscriptionPlan $plan, $amount = null)
    {
        $this->organization = $organization;
        $this->plan = $plan;
        $this->amount = $amount;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🚀 ¡NUEVA SUSCRIPCIÓN VENDIDA!: ' . $this->organization->name . ' - ' . $this->plan->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-paid',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
