<?php

namespace App\Models;

use App\Enums\StockMovementType;
use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ledger append-only. No lleva SoftDeletes ni updated_at a proposito: una fila
 * de aca no se edita ni se borra nunca. Crearlas SIEMPRE via App\Services\StockService,
 * nunca con StockMovement::create() directo, para que el cache en ProductStock
 * se mantenga consistente.
 */
class StockMovement extends Model
{
    use HasFactory, HasOrganization;

    const UPDATED_AT = null;

    protected $fillable = [
        'organization_id',
        'product_id',
        'type',
        'quantity_delta',
        'unit_cost',
        'reference_type',
        'reference_id',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'type' => StockMovementType::class,
        'quantity_delta' => 'integer',
        'unit_cost' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
