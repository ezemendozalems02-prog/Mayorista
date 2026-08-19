<?php

namespace App\Enums;

enum RepairStatus: string
{
    case PENDING = 'pending';
    case DIAGNOSIS = 'diagnosis';
    case QUOTED = 'quoted';
    case APPROVED = 'approved';
    case IN_PROGRESS = 'in_progress';
    case READY = 'ready';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
}
