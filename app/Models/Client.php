<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes, HasOrganization, Auditable;

    protected $fillable = [
        'organization_id',
        'full_name',
        'phone',
        'email',
        'document_number',
        'notes',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function repairs(): HasMany
    {
        return $this->hasMany(Repair::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
