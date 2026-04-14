<?php

namespace App\Http\Middleware;

use App\Models\Subscription;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlanLimits
{
    /**
     * Handle an incoming request.
     * Injects the current tenant's plan into request attributes.
     * Does not block — only enriches the request.
     *
     * Not using Cache here because Stancl's CacheTenancyBootstrapper wraps all
     * cache calls with tags(), which the `file` driver does not support. The
     * query is cheap (indexed on tenant_id) so we just run it per request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (function_exists('tenant') && tenant()) {
            $subscription = Subscription::where('tenant_id', tenant()->id)
                ->latest()
                ->first();

            $plan = $subscription?->plan;

            if ($plan) {
                $request->attributes->set('tenantPlan', $plan);
            }
        }

        return $next($request);
    }
}
