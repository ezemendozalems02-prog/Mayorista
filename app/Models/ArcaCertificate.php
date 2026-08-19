<?php

namespace App\Models;

use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArcaCertificate extends Model
{
    use HasFactory, HasOrganization;

    protected $fillable = [
        'organization_id',
        'certificate',
        'private_key',
        'certificate_alias',
        'expires_at',
    ];

    protected $casts = [
        'certificate' => 'encrypted',
        'private_key' => 'encrypted',
        'expires_at' => 'datetime',
    ];

    // Never expose sensitive fields in API responses
    protected $hidden = ['certificate', 'private_key'];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
