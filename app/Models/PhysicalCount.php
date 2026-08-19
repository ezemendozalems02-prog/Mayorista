<?php

namespace App\Models;

use App\Enums\PhysicalCountStatus;
use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhysicalCount extends Model
{
    use HasFactory, HasOrganization;

    protected $attributes = [
        'status' => 'open', // Postgres no devuelve defaults de columna al insertar; ver Product.php
    ];

    protected $fillable = [
        'organization_id',
        'code',
        'category_id',
        'status',
        'notes',
        'created_by',
        'completed_at',
    ];

    protected $casts = [
        'status' => PhysicalCountStatus::class,
        'completed_at' => 'datetime',
    ];

    /**
     * Genera el codigo (INV-000001) a partir del id, una sola vez, igual que
     * Product::internal_code. Ver ese modelo para el razonamiento completo.
     */
    protected static function booted(): void
    {
        static::created(function (PhysicalCount $count) {
            if (empty($count->code)) {
                $count->forceFill([
                    'code' => 'INV-' . str_pad((string) $count->id, 6, '0', STR_PAD_LEFT),
                ])->saveQuietly();
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PhysicalCountItem::class);
    }
}
