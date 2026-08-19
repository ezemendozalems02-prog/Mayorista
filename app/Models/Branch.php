<?php

namespace App\Models;

use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes, HasOrganization;

    protected $fillable = [
        'organization_id',
        'name',
        'address',
        'phone',
        'email',
        'manager_name',
        'is_active',
        'is_main',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_main'   => 'boolean',
    ];

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function repairs(): HasMany
    {
        return $this->hasMany(Repair::class);
    }

    /**
     * Get total stock count for this branch.
     */
    public function stockCount(): int
    {
        return $this->inventoryItems()
            ->where('status', '!=', 'sold')
            ->where('status', '!=', 'archived')
            ->count();
    }

    /**
     * Get total sales this month.
     */
    public function salesThisMonth(): float
    {
        return $this->sales()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');
    }
}
