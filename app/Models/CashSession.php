<?php

namespace App\Models;

use App\Enums\CashSessionStatus;
use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Turno de caja (Fase 14). Crear/cerrar SIEMPRE via App\Services\CashService,
 * nunca con CashSession::create()/update() directo -- ahi vive la logica de
 * "una sola caja abierta por organizacion" y el calculo del arqueo.
 */
class CashSession extends Model
{
    use HasFactory, HasOrganization;

    protected $attributes = [
        'status' => 'open', // Postgres no devuelve defaults de columna al insertar; ver Product.php
    ];

    protected $fillable = [
        'organization_id',
        'status',
        'opening_amount',
        'closing_amount',
        'expected_amount',
        'difference',
        'opened_by',
        'closed_by',
        'notes',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'status' => CashSessionStatus::class,
        'opening_amount' => 'decimal:2',
        'closing_amount' => 'decimal:2',
        'expected_amount' => 'decimal:2',
        'difference' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }

    public function isOpen(): bool
    {
        return $this->status === CashSessionStatus::OPEN;
    }
}
