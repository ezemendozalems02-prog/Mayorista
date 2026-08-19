<?php

namespace App\Models;

use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiscalSetting extends Model
{
    use HasFactory, HasOrganization;

    protected $fillable = [
        'organization_id',
        'cuit',
        'razon_social',
        'condicion_iva',
        'punto_venta',
        'tipo_comprobante_default',
        'ambiente',
        'motor_integracion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'punto_venta' => 'integer',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
