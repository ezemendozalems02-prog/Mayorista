<?php

namespace App\Models;

use App\Enums\ClientType;
use App\Traits\Auditable;
use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes, HasOrganization, Auditable;

    /**
     * Postgres no devuelve los defaults de columna al insertar; ver Product.php
     * para el razonamiento completo.
     */
    protected $attributes = [
        'client_type' => 'retail',
        'discount_percentage' => 0,
    ];

    protected $fillable = [
        'organization_id',
        'full_name',
        'client_type',
        'business_name',
        'phone',
        'email',
        'document_number',
        'cuit',
        'address',
        'credit_limit',
        'discount_percentage',
        'price_list_id',
        'notes',
    ];

    protected $casts = [
        'client_type' => ClientType::class,
        'credit_limit' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
    ];

    /**
     * Nombre para mostrar: razon social si es mayorista y la cargo,
     * si no el nombre de contacto de siempre.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->business_name ?: $this->full_name;
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function repairs(): HasMany
    {
        return $this->hasMany(Repair::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
