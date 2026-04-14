<?php

declare(strict_types=1);

namespace App\Actions\Subscription;

use App\Models\Plan;
use App\Models\Tenant;
use RuntimeException;

class StartCheckout
{
    /**
     * Initiate a Stripe Checkout session for the given tenant and plan.
     * Returns the Stripe Checkout URL.
     *
     * @throws RuntimeException
     */
    public function execute(Tenant $tenant, Plan $plan, string $successUrl, string $cancelUrl): string
    {
        if (empty($plan->stripe_price_id)) {
            throw new RuntimeException("El plan '{$plan->name}' no tiene un precio de Stripe configurado.");
        }

        // Check if already subscribed to the same plan
        $currentSub = $tenant->subscription;
        if ($currentSub && ! $currentSub->isFree() && $currentSub->plan_id === $plan->id) {
            throw new RuntimeException("Ya estás suscrito al plan '{$plan->name}'.");
        }

        // Remove the free placeholder subscription so Cashier can create a fresh one
        if ($currentSub && $currentSub->isFree()) {
            $currentSub->delete();
        }

        $checkout = $tenant->newSubscription('default', $plan->stripe_price_id)
            ->trialDays($plan->trial_days ?? 0)
            ->checkout([
                'success_url' => $successUrl.'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $cancelUrl,
                'metadata' => [
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                    'plan_slug' => $plan->slug,
                ],
            ]);

        return $checkout->url;
    }
}
