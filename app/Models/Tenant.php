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
            'onboarding_completed_at',
            'onboarding_step',
        ];
    }

    protected $casts = [
        'onboarding_completed_at' => 'datetime',
        'onboarding_step' => 'integer',
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
