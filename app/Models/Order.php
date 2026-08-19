<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pedido (Fase 17). Crear/confirmar/cumplir/cancelar SIEMPRE via
 * App\Services\OrderService, nunca con Order::create()/update() directo.
 */
class Order extends Model
{
    use HasFactory, HasOrganization;

    protected $attributes = [
        'status' => 'draft', // Postgres no devuelve defaults de columna al insertar; ver Product.php
    ];

    protected $fillable = [
        'organization_id',
        'client_id',
        'code',
        'status',
        'subtotal',
        'discount',
        'total',
        'notes',
        'created_by',
        'sale_id',
        'fulfilled_at',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'fulfilled_at' => 'datetime',
    ];

    /**
     * Genera el codigo (ORD-000001) a partir del id, una sola vez, igual que
     * Purchase::code (Fase 12).
     */
    protected static function booted(): void
    {
        static::created(function (Order $order) {
            if (empty($order->code)) {
                $order->forceFill([
                    'code' => 'ORD-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
                ])->saveQuietly();
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
