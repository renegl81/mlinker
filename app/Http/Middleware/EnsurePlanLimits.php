<?php

namespace App\Http\Middleware;

use App\Models\Subscription;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlanLimits
{
    /**
     * Handle an incoming request.
     * Injects the current tenant's plan into request attributes.
     * Does not block — only enriches the request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (function_exists('tenant') && tenant()) {
            $tenantId = tenant()->id;

            $plan = Cache::remember("tenant:{$tenantId}:plan", 600, function () use ($tenantId) {
                $subscription = Subscription::where('tenant_id', $tenantId)
                    ->latest()
                    ->first();

                return $subscription?->plan;
            });

            if ($plan) {
                $request->attributes->set('tenantPlan', $plan);
            }
        }

        return $next($request);
    }
}
