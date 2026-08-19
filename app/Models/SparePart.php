<?php

namespace App\Models;

use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SparePart extends Model
{
    use HasFactory, SoftDeletes, HasOrganization;

    protected $fillable = [
        'organization_id',
        'name',
        'sku',
        'stock',
        'cost_price',
        'sale_price',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'stock' => 'integer',
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function repairParts(): HasMany
    {
        return $this->hasMany(RepairPart::class);
    }
}
