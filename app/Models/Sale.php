<?php

namespace App\Models;

use App\Traits\HasOrganization;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes, HasOrganization, Auditable;

    protected $fillable = [
        'organization_id',
        'branch_id',
        'client_id',
        'seller_id',
        'sale_number',
        'status',
        'currency',
        'exchange_rate',
        'subtotal',
        'discount',
        'total',
        'cost_total',
        'profit_total',
        'notes',
        'sold_at',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'cost_total' => 'decimal:2',
        'profit_total' => 'decimal:2',
        'sold_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function tradeIn(): BelongsTo
    {
        return $this->belongsTo(TradeIn::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
