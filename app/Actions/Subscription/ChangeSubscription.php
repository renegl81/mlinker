<?php

declare(strict_types=1);

namespace App\Actions\Subscription;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use RuntimeException;

class ChangeSubscription
{
    private function getActiveSubscription(Tenant $tenant): ?Subscription
    {
        return Subscription::where('tenant_id', $tenant->id)
            ->where('type', 'default')
            ->latest()
            ->first();
    }

    /**
     * Swap the tenant's subscription to a new plan.
     */
    public function swap(Tenant $tenant, Plan $newPlan): void
    {
        if (empty($newPlan->stripe_price_id)) {
            throw new RuntimeException("El plan '{$newPlan->name}' no tiene un precio de Stripe configurado.");
        }

        $subscription = $this->getActiveSubscription($tenant);

        if (! $subscription || $subscription->isFree()) {
            throw new RuntimeException('No hay una suscripción de pago activa para cambiar.');
        }

        $subscription->swap($newPlan->stripe_price_id);

        // Update local plan reference
        $subscription->update(['plan_id' => $newPlan->id]);
    }

    /**
     * Cancel the tenant's subscription at period end.
     */
    public function cancel(Tenant $tenant): void
    {
        $subscription = $this->getActiveSubscription($tenant);

        if (! $subscription || $subscription->isFree()) {
            throw new RuntimeException('No hay una suscripción de pago activa para cancelar.');
        }

        $subscription->cancel();
    }

    /**
     * Resume a cancelled subscription (before period end).
     */
    public function resume(Tenant $tenant): void
    {
        $subscription = $this->getActiveSubscription($tenant);

        if (! $subscription) {
            throw new RuntimeException('No hay ninguna suscripción para reanudar.');
        }

        $subscription->resume();
    }
}
