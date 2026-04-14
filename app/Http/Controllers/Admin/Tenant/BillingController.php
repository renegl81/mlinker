<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Tenant;

use App\Actions\Subscription\ChangeSubscription;
use App\Actions\Subscription\StartCheckout;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class BillingController extends Controller
{
    public function plans(): Response
    {
        $tenant = tenancy()->tenant;

        $plans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $currentSubscription = $tenant->subscription()->with('plan')->first();

        return Inertia::render('admin/tenant/billing/Plans', [
            'plans' => $plans,
            'currentSubscription' => $currentSubscription,
        ]);
    }

    public function checkout(Request $request, StartCheckout $action): RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        $request->validate([
            'plan_slug' => ['required', 'string', 'exists:plans,slug'],
        ]);

        $plan = Plan::where('slug', $request->plan_slug)->firstOrFail();

        if (empty($plan->stripe_price_id)) {
            return back()->with('error', "El plan '{$plan->name}' no está disponible para suscripción online. Por favor contacta con nosotros.");
        }

        $tenant = tenancy()->tenant;

        try {
            $successUrl = route('tenant.billing.success');
            $cancelUrl = route('tenant.billing.plans');

            $checkoutUrl = $action->execute($tenant, $plan, $successUrl, $cancelUrl);

            return Inertia::location($checkoutUrl);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Stripe checkout error', [
                'tenant_id' => $tenant->id,
                'plan_slug' => $request->plan_slug,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Ha ocurrido un error al iniciar el pago. Por favor, inténtalo de nuevo.');
        }
    }

    public function success(): Response
    {
        return Inertia::render('admin/tenant/billing/Success');
    }

    public function manage(): Response
    {
        $tenant = tenancy()->tenant;

        $subscription = $tenant->subscription()->with('plan')->first();

        return Inertia::render('admin/tenant/billing/Manage', [
            'subscription' => $subscription,
        ]);
    }

    public function cancel(ChangeSubscription $action): RedirectResponse
    {
        $tenant = tenancy()->tenant;

        try {
            $action->cancel($tenant);

            return back()->with('success', 'Tu suscripción se cancelará al final del período de facturación.');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function resume(ChangeSubscription $action): RedirectResponse
    {
        $tenant = tenancy()->tenant;

        try {
            $action->resume($tenant);

            return back()->with('success', 'Tu suscripción ha sido reactivada.');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
