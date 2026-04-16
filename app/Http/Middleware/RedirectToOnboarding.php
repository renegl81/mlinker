<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToOnboarding
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            tenancy()->initialized
            && tenant('onboarding_completed_at') === null
            && ! $request->routeIs('tenant.onboarding.*')
            && ! $request->routeIs('tenant.logout')
            && ! str_starts_with($request->path(), 'panel/onboarding')
        ) {
            return redirect()->route('tenant.onboarding.show');
        }

        return $next($request);
    }
}
