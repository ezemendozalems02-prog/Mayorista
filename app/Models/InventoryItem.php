<?php

namespace App\Models;

use App\Enums\InventoryStatus;
use App\Traits\HasOrganization;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use HasFactory, SoftDeletes, HasOrganization, Auditable;

    protected $fillable = [
        'organization_id',
        'branch_id',
        'category',
        'brand',
        'model',
        'storage',
        'color',
        'imei',
        'serial_number',
        'battery_health',
        'cosmetic_condition',
        'purchase_price',
        'sale_price',
        'currency',
        'status',
        'stock_type',
        'client_id',
        'notes',
    ];

    protected $casts = [
        'battery_health' => 'integer',
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'status' => InventoryStatus::class,
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
