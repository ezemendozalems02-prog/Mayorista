<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'promo_price',
        'promo_ends_at',
        'currency',
        'billing_interval',
        'is_active',
        'features_json',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'promo_price' => 'decimal:2',
        'promo_ends_at' => 'datetime',
        'is_active' => 'boolean',
        'features_json' => 'array',
    ];

    /**
     * Get current effective price.
     */
    public function getEffectivePriceAttribute()
    {
        if ($this->promo_price && $this->promo_ends_at && now()->lt($this->promo_ends_at)) {
            return $this->promo_price;
        }
        return $this->price;
    }

    public function isOnSale(): bool
    {
        return $this->promo_price && $this->promo_ends_at && now()->lt($this->promo_ends_at);
    }

    public function features(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }
}
