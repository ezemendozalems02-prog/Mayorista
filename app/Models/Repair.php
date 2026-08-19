<?php

namespace App\Models;

use App\Enums\RepairPriority;
use App\Enums\RepairStatus;
use App\Traits\Auditable;
use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Repair extends Model
{
    use HasFactory, SoftDeletes, HasOrganization, Auditable;

    protected $fillable = [
        'organization_id',
        'branch_id',
        'client_id',
        'technician_id',
        'repair_number',
        'device_brand',
        'device_model',
        'imei',
        'serial_number',
        'status',
        'priority',
        'reported_issue',
        'diagnosis',
        'estimated_cost',
        'final_cost',
        'deposit_amount',
        'warranty_days',
        'internal_notes',
        'received_at',
        'delivered_at',
    ];

    protected $casts = [
        'status' => RepairStatus::class,
        'priority' => RepairPriority::class,
        'estimated_cost' => 'decimal:2',
        'final_cost' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'received_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class);
    }

    public function repairParts(): HasMany
    {
        return $this->hasMany(RepairPart::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
