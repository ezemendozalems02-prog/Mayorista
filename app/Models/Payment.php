<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes, HasOrganization;

    protected $fillable = [
        'organization_id',
        'sale_id',
        'repair_id',
        'client_id',
        'type',
        'method',
        'currency',
        'amount',
        'exchange_rate',
        'notes',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:2',
        'paid_at' => 'datetime',
        'method' => PaymentMethod::class,
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function repair(): BelongsTo
    {
        return $this->belongsTo(Repair::class);
    }
}
