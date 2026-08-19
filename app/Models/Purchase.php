<?php

namespace App\Models;

use App\Enums\PurchaseStatus;
use App\Traits\Auditable;
use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory, SoftDeletes, HasOrganization, Auditable;

    protected $attributes = [
        'status' => 'pending', // Postgres no devuelve defaults de columna al insertar; ver Product.php
    ];

    protected $fillable = [
        'organization_id',
        'supplier_id',
        'code',
        'status',
        'subtotal',
        'discount',
        'total',
        'notes',
        'created_by',
        'received_at',
    ];

    protected $casts = [
        'status' => PurchaseStatus::class,
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'received_at' => 'datetime',
    ];

    /**
     * Genera el codigo (PO-000001) a partir del id, una sola vez, igual que
     * Product::internal_code y PhysicalCount::code.
     */
    protected static function booted(): void
    {
        static::created(function (Purchase $purchase) {
            if (empty($purchase->code)) {
                $purchase->forceFill([
                    'code' => 'PO-' . str_pad((string) $purchase->id, 6, '0', STR_PAD_LEFT),
                ])->saveQuietly();
            }
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
