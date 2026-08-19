<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductSupplier extends Pivot
{
    protected $table = 'product_supplier';

    public $incrementing = true;

    protected $fillable = [
        'product_id',
        'supplier_id',
        'supplier_sku',
        'cost',
        'is_primary',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'is_primary' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
