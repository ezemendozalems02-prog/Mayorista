<?php

namespace App\Models;

use App\Enums\ProductStatus;
use App\Traits\Auditable;
use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, HasOrganization, Auditable;

    /**
     * Postgres no devuelve los defaults de columna al insertar (a diferencia de lo que
     * uno podria asumir); sin este default en PHP, $product->status quedaria null en
     * memoria justo despues de crear un producto sin pasar 'status' explicitamente,
     * aunque la fila en la base ya tenga 'active' gracias al default de la migracion.
     */
    protected $attributes = [
        'status' => 'active',
    ];

    protected $fillable = [
        'organization_id',
        'category_id',
        'brand_id',
        'internal_code',
        'barcode',
        'name',
        'description',
        'cost',
        'retail_price',
        'wholesale_price',
        'min_stock',
        'image_path',
        'status',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'retail_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'min_stock' => 'integer',
        'status' => ProductStatus::class,
    ];

    /**
     * Genera el internal_code (MIT-000001) a partir del id, una sola vez,
     * apenas el producto tiene un id asignado. No usa una tabla de contador:
     * el propio id autoincremental garantiza unicidad sin condiciones de carrera.
     * Ver "Esquema Mito" (diseño de datos, Fase 5) para el razonamiento completo.
     */
    protected static function booted(): void
    {
        static::created(function (Product $product) {
            if (empty($product->internal_code)) {
                $product->forceFill([
                    'internal_code' => 'MIT-' . str_pad((string) $product->id, 6, '0', STR_PAD_LEFT),
                ])->saveQuietly();
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'product_supplier')
            ->using(ProductSupplier::class)
            ->withPivot(['supplier_sku', 'cost', 'is_primary'])
            ->withTimestamps();
    }

    public function stock(): HasOne
    {
        return $this->hasOne(ProductStock::class);
    }

    public function priceListItems(): HasMany
    {
        return $this->hasMany(PriceListItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Cantidad actual en stock. 0 si todavia no existe la fila cacheada
     * (producto sin ningun movimiento registrado).
     */
    public function getCurrentStockAttribute(): int
    {
        return $this->stock?->quantity ?? 0;
    }
}
