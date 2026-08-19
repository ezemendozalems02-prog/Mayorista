<?php

namespace App\Notifications;

use App\Models\Repair;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RepairCompletedNotification extends Notification
{
    use Queueable;

    protected $repair;

    public function __construct(Repair $repair)
    {
        $this->repair = $repair;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'repair_completed',
            'repair_id' => $this->repair->id,
            'repair_number' => $this->repair->repair_number,
            'device_model' => $this->repair->device_model,
            'technician_name' => $this->repair->technician->name ?? 'Técnico',
            'title' => '¡Reparación Finalizada!',
            'message' => "El equipo {$this->repair->device_model} (Ticket #{$this->repair->repair_number}) ha sido marcado como finalizado por el técnico.",
            'url' => route('repair.show', $this->repair->id),
            'icon' => 'wrench'
        ];
    }
}
