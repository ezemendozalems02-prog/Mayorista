<?php

namespace App\Models;

use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArcaToken extends Model
{
    use HasFactory, HasOrganization;

    protected $fillable = [
        'organization_id',
        'token',
        'sign',
        'expiration',
        'environment',
    ];

    protected $casts = [
        'token' => 'encrypted',
        'sign' => 'encrypted',
        'expiration' => 'datetime',
    ];

    // Never expose sensitive fields in API responses
    protected $hidden = ['token', 'sign'];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function isExpired(): bool
    {
        return $this->expiration && $this->expiration->isPast();
    }

    public function isValid(): bool
    {
        return ! $this->isExpired() && $this->token !== null && $this->sign !== null;
    }
}
