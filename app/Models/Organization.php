<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'phone',
        'email',
        'country',
        'currency',
        'is_active',
        'trial_ends_at',
        'subscription_status',
        'subscription_ends_at',
        'plan',
        'subscription_plan_id',
        'notifications_email_enabled',
        'notifications_email_alias',
        'is_demo',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'notifications_email_enabled' => 'boolean',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
    ];

    /**
     * Check if the organization has access to a specific feature.
     */
    public function hasFeature(string $feature): bool
    {
        // ── Platform Mode Bypass ─────────────────────────────────────────────
        // In single_license or white_label mode, all features are enabled
        $platformMode = config('platform.mode', 'saas');
        if ($platformMode !== 'saas') {
            return true;
        }
        // ─────────────────────────────────────────────────────────────────────

        // During trial, all features are enabled
        if ($this->subscription_status === 'trial' && $this->trial_ends_at && now()->lt($this->trial_ends_at)) {
            return true;
        }

        // If subscription is expired or cancelled, no premium features
        if ($this->subscription_status !== 'active' && $this->trial_ends_at && now()->gt($this->trial_ends_at)) {
            return false;
        }

        // Logic using database-stored features
        if ($this->subscription_plan_id) {
            $checkFeatures = [$feature];
            
            // Hierarchy: Pro features include Basic features
            if ($feature === 'inventory_basic') $checkFeatures[] = 'inventory_unlimited';
            if ($feature === 'reports_basic') $checkFeatures[] = 'reports_advanced';

            return $this->subscriptionPlan->features()
                ->whereIn('feature_name', $checkFeatures)
                ->where('feature_value', 'true')
                ->exists();
        }

        // Fallback to legacy hardcoded logic if no dynamic plan is set yet
        $planFeatures = [
            'basic' => [
                'inventory_basic',
                'sales',
                'clients',
                'reports_basic',
            ],
            'pro' => [
                'inventory_basic',
                'inventory_unlimited',
                'sales',
                'clients',
                'reports_basic',
                'reports_advanced',
                'repairs',
                'technicians',
                'trade_ins',
                'multi_branch',
                'arca_billing',
            ]
        ];

        $currentPlan = $this->plan ?? 'basic';
        return in_array($feature, $planFeatures[$currentPlan] ?? []);
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function repairs(): HasMany
    {
        return $this->hasMany(Repair::class);
    }

    public function technicians(): HasMany
    {
        return $this->hasMany(Technician::class);
    }

    public function spareParts(): HasMany
    {
        return $this->hasMany(SparePart::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // ── ARCA Facturación ─────────────────────────────────────────────────────

    public function fiscalSetting()
    {
        return $this->hasOne(FiscalSetting::class);
    }

    public function arcaCertificate()
    {
        return $this->hasOne(ArcaCertificate::class);
    }

    public function arcaTokens(): HasMany
    {
        return $this->hasMany(ArcaToken::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function arcaLogs(): HasMany
    {
        return $this->hasMany(ArcaLog::class);
    }
}
