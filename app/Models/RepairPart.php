<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepairPart extends Model
{
    use HasFactory;

    protected $fillable = [
        'repair_id',
        'spare_part_id',
        'quantity',
        'unit_cost',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function repair(): BelongsTo
    {
        return $this->belongsTo(Repair::class);
    }

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }
}
