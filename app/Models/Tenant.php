<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    use Billable, HasApiTokens, HasDomains;

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'onboarding_completed_at',
            'onboarding_step',
            'has_website',
            'business_type',
            'home_template',
        ];
    }

    protected $casts = [
        'onboarding_completed_at' => 'datetime',
        'onboarding_step' => 'integer',
        'has_website' => 'boolean',
    ];

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latest();
    }

    /**
     * Helper rápido para saber si es Premium
     */
    public function isPremium(): bool
    {
        return $this->subscription && $this->subscription->isActive();
    }

    /**
     * Email for Stripe customer — uses the owner's email.
     */
    public function stripeEmail(): ?string
    {
        return $this->users()
            ->wherePivot('role', 'owner')
            ->first()
            ?->email;
    }

    /**
     * Name for Stripe customer — uses the tenant id (business name).
     */
    public function stripeName(): string
    {
        return ucfirst($this->id);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'tenant_user',
            'tenant_id',
            'user_id'
        )
            ->withPivot('role', 'permissions', 'is_active', 'invited_at', 'joined_at')
            ->withTimestamps();
    }
}
