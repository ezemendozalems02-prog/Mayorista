<?php

namespace App\Traits;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait HasOrganization
{
    protected static function bootHasOrganization(): void
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $user = Auth::user();
                if ($user->role !== \App\Enums\UserRole::SUPER_ADMIN) {
                    $model->organization_id = $user->organization_id;
                }
            }
        });

        static::addGlobalScope('organization', function (Builder $builder) {
            if (Auth::check()) {
                $user = Auth::user();
                if ($user->role !== \App\Enums\UserRole::SUPER_ADMIN) {
                    $builder->where($builder->getQuery()->from . '.organization_id', $user->organization_id);
                }
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
