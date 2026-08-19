<?php

namespace App\Models;

use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TradeIn extends Model
{
    use HasFactory, HasOrganization;

    protected $fillable = [
        'organization_id',
        'sale_id',
        'client_id',
        'inventory_item_id',
        'brand',
        'model',
        'storage',
        'imei',
        'battery_health',
        'condition',
        'appraised_value',
        'currency',
        'notes',
    ];

    protected $casts = [
        'appraised_value' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
