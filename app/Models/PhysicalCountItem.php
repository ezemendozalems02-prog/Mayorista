<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhysicalCountItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'physical_count_id',
        'product_id',
        'expected_quantity',
        'counted_quantity',
        'counted_at',
        'notes',
    ];

    protected $casts = [
        'expected_quantity' => 'integer',
        'counted_quantity' => 'integer',
        'counted_at' => 'datetime',
    ];

    public function getDifferenceAttribute(): ?int
    {
        return $this->counted_quantity === null ? null : $this->counted_quantity - $this->expected_quantity;
    }

    public function physicalCount(): BelongsTo
    {
        return $this->belongsTo(PhysicalCount::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
