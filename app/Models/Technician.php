<?php

namespace App\Models;

use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Technician extends Model
{
    use HasFactory, SoftDeletes, HasOrganization;

    protected $fillable = [
        'organization_id',
        'user_id',
        'full_name',
        'phone',
        'email',
        'specialties',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function repairs(): HasMany
    {
        return $this->hasMany(Repair::class);
    }
}
