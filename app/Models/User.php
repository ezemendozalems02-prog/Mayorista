<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'is_demo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'role' => \App\Enums\UserRole::class,
        ];
    }

    public function organization(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === \App\Enums\UserRole::SUPER_ADMIN;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasRole($roles): bool
    {
        if ($this->isSuperAdmin()) return true;

        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->checkRole($role)) return true;
            }
            return false;
        }

        return $this->checkRole($roles);
    }

    private function checkRole($role): bool
    {
        $roleValue = $role instanceof \UnitEnum ? $role->value : $role;
        $userRoleValue = $this->role instanceof \UnitEnum ? $this->role->value : $this->role;
        
        return $userRoleValue === $roleValue;
    }
}
