<?php

namespace App\Models;

use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Cache derivado de la suma de stock_movements para un producto. Nunca se
 * escribe directamente: solo App\Services\StockService la actualiza, dentro
 * de la misma transaccion que crea el StockMovement correspondiente.
 */
class ProductStock extends Model
{
    use HasFactory, HasOrganization;

    protected $attributes = [
        'quantity' => 0, // Postgres no devuelve defaults de columna al insertar; ver Product.php
    ];

    protected $fillable = [
        'organization_id',
        'product_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
