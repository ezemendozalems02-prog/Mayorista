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
                    $builder->where(static::qualifiedOrganizationColumn($builder), $user->organization_id);
                }
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Resuelve "tabla.organization_id" para el where del scope global, incluso cuando
     * el builder tiene un alias (ej. "categories as laravel_reserved_0", que Eloquent
     * genera con withCount()/withAggregate() en relaciones auto-referenciadas como
     * Category::children()). Sin esto, la concatenacion literal del nombre de tabla
     * produce SQL invalido ("categories" as "laravel_reserved_0.organization_id").
     */
    protected static function qualifiedOrganizationColumn(Builder $builder): string
    {
        $table = $builder->getQuery()->from;

        if ($table instanceof \Illuminate\Database\Query\Expression) {
            $table = $table->getValue($builder->getQuery()->getGrammar());
        }

        if (is_string($table) && preg_match('/\s+as\s+(\S+)$/i', $table, $matches)) {
            $table = $matches[1];
        }

        return $table . '.organization_id';
    }
}
