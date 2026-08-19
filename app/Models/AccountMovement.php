<?php

namespace App\Models;

use App\Enums\AccountMovementType;
use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ledger append-only. No lleva SoftDeletes ni updated_at a proposito, igual
 * que StockMovement. Crear SIEMPRE via App\Services\AccountService, nunca
 * con AccountMovement::create() directo.
 */
class AccountMovement extends Model
{
    use HasFactory, HasOrganization;

    const UPDATED_AT = null;

    protected $fillable = [
        'organization_id',
        'client_id',
        'type',
        'amount',
        'reference_type',
        'reference_id',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'type' => AccountMovementType::class,
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
