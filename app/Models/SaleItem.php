<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'inventory_item_id',
        'item_name',
        'unit_cost',
        'unit_price',
        'quantity',
        'line_total',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'quantity' => 'integer',
        'line_total' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
