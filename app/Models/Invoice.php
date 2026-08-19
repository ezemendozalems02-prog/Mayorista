<?php

namespace App\Models;

use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory, HasOrganization;

    protected $fillable = [
        'organization_id',
        'cliente_nombre',
        'cliente_documento',
        'cliente_condicion_iva',
        'tipo_comprobante',
        'punto_venta',
        'numero_comprobante',
        'subtotal',
        'iva',
        'total',
        'cae',
        'cae_vencimiento',
        'estado',
        'error_message',
        'arca_response',
        'pdf_path',
        'pdf_generated_at',
    ];

    protected $casts = [
        'subtotal'         => 'decimal:2',
        'iva'              => 'decimal:2',
        'total'            => 'decimal:2',
        'cae_vencimiento'  => 'date',
        'arca_response'    => 'array',
        'punto_venta'      => 'integer',
        'numero_comprobante' => 'integer',
        'pdf_generated_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ArcaLog::class);
    }

    public function isAuthorized(): bool
    {
        return $this->estado === 'AUTORIZADO';
    }
}
