<?php

namespace App\Models;

use App\Enums\CashMovementType;
use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ledger append-only. No lleva SoftDeletes ni updated_at a proposito, igual
 * que StockMovement y AccountMovement. Crear SIEMPRE via
 * App\Services\CashService, nunca con CashMovement::create() directo.
 */
class CashMovement extends Model
{
    use HasFactory, HasOrganization;

    const UPDATED_AT = null;

    protected $fillable = [
        'organization_id',
        'cash_session_id',
        'type',
        'amount',
        'reference_type',
        'reference_id',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'type' => CashMovementType::class,
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function cashSession(): BelongsTo
    {
        return $this->belongsTo(CashSession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
