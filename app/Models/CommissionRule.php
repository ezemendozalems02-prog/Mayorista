<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'amount',
        'is_recurring',
        'trigger_event',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_recurring' => 'boolean',
    ];
}
