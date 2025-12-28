<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Cashier\Billable;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    use Billable, HasDomains;

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
