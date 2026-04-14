<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends WebhookController
{
    /**
     * Handle a Stripe subscription created event.
     * Syncs plan_id and cleans up the old free subscription.
     */
    public function handleCustomerSubscriptionCreated(array $payload): Response
    {
        $response = parent::handleCustomerSubscriptionCreated($payload);

        try {
            $stripeSubscription = $payload['data']['object'];
            $customerId = $stripeSubscription['customer'] ?? null;
            $stripeSubscriptionId = $stripeSubscription['id'];
            $items = $stripeSubscription['items']['data'] ?? [];
            $stripePriceId = $items[0]['price']['id'] ?? null;

            if (! $customerId || ! $stripePriceId) {
                return $response;
            }

            $tenant = Tenant::where('stripe_id', $customerId)->first();
            if (! $tenant) {
                return $response;
            }

            $plan = Plan::where('stripe_price_id', $stripePriceId)->first();
            if (! $plan) {
                Log::warning('StripeWebhook: no plan found for stripe_price_id', [
                    'stripe_price_id' => $stripePriceId,
                ]);

                return $response;
            }

            // Delete the old free subscription (has stripe_id = null)
            Subscription::where('tenant_id', $tenant->id)
                ->whereNull('stripe_id')
                ->delete();

            // Set plan_id on the new Cashier-created subscription
            Subscription::where('stripe_id', $stripeSubscriptionId)
                ->update(['plan_id' => $plan->id]);

            Log::info('StripeWebhook: subscription created, plan synced', [
                'tenant_id' => $tenant->id,
                'stripe_subscription_id' => $stripeSubscriptionId,
                'plan_slug' => $plan->slug,
            ]);
        } catch (\Throwable $e) {
            Log::error('StripeWebhook: error in handleCustomerSubscriptionCreated', [
                'error' => $e->getMessage(),
            ]);
        }

        return $response;
    }

    /**
     * Handle a Stripe subscription updated event.
     * Syncs the local plan_id based on the active Stripe price.
     */
    public function handleCustomerSubscriptionUpdated(array $payload): Response
    {
        $response = parent::handleCustomerSubscriptionUpdated($payload);

        try {
            $stripeSubscription = $payload['data']['object'];
            $stripeSubscriptionId = $stripeSubscription['id'];
            $items = $stripeSubscription['items']['data'] ?? [];

            if (empty($items)) {
                return $response;
            }

            $stripePriceId = $items[0]['price']['id'] ?? null;

            if (! $stripePriceId) {
                return $response;
            }

            $plan = Plan::where('stripe_price_id', $stripePriceId)->first();

            if (! $plan) {
                Log::warning('StripeWebhook: no plan found for stripe_price_id', [
                    'stripe_price_id' => $stripePriceId,
                    'stripe_subscription_id' => $stripeSubscriptionId,
                ]);

                return $response;
            }

            Subscription::where('stripe_id', $stripeSubscriptionId)
                ->update(['plan_id' => $plan->id]);

            Log::info('StripeWebhook: subscription updated, plan synced', [
                'stripe_subscription_id' => $stripeSubscriptionId,
                'plan_slug' => $plan->slug,
            ]);
        } catch (\Throwable $e) {
            Log::error('StripeWebhook: error in handleCustomerSubscriptionUpdated', [
                'error' => $e->getMessage(),
            ]);
        }

        return $response;
    }

    /**
     * Handle a Stripe subscription deleted event.
     * Degrades the tenant to the Free plan.
     */
    public function handleCustomerSubscriptionDeleted(array $payload): Response
    {
        $response = parent::handleCustomerSubscriptionDeleted($payload);

        try {
            $stripeSubscription = $payload['data']['object'];
            $stripeSubscriptionId = $stripeSubscription['id'];

            $freePlan = Plan::free();

            if (! $freePlan) {
                Log::error('StripeWebhook: Free plan not found in database');

                return $response;
            }

            $subscription = Subscription::where('stripe_id', $stripeSubscriptionId)->first();

            if ($subscription) {
                $subscription->update([
                    'plan_id' => $freePlan->id,
                    'stripe_status' => 'free',
                    'stripe_id' => null,
                    'stripe_price' => null,
                    'ends_at' => null,
                    'trial_ends_at' => null,
                ]);

                Log::info('StripeWebhook: subscription deleted, tenant degraded to Free', [
                    'stripe_subscription_id' => $stripeSubscriptionId,
                    'tenant_id' => $subscription->tenant_id,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('StripeWebhook: error in handleCustomerSubscriptionDeleted', [
                'error' => $e->getMessage(),
            ]);
        }

        return $response;
    }

    /**
     * Handle a Stripe invoice payment failed event.
     */
    public function handleInvoicePaymentFailed(array $payload): Response
    {
        $response = parent::handleInvoicePaymentFailed($payload);

        try {
            $invoice = $payload['data']['object'];
            $customerId = $invoice['customer'] ?? null;
            $subscriptionId = $invoice['subscription'] ?? null;

            Log::warning('StripeWebhook: invoice payment failed', [
                'customer_id' => $customerId,
                'subscription_id' => $subscriptionId,
                'invoice_id' => $invoice['id'] ?? null,
                'amount_due' => $invoice['amount_due'] ?? null,
            ]);

            // Find tenant and log for future mailable integration
            if ($customerId) {
                $tenant = Tenant::where('stripe_id', $customerId)->first();
                if ($tenant) {
                    Log::warning('StripeWebhook: payment failed for tenant', [
                        'tenant_id' => $tenant->id,
                    ]);
                    // TODO: dispatch PaymentFailedMail to tenant owner(s)
                }
            }
        } catch (\Throwable $e) {
            Log::error('StripeWebhook: error in handleInvoicePaymentFailed', [
                'error' => $e->getMessage(),
            ]);
        }

        return $response;
    }
}
