<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Tenant|null $tenant */
        $tenant = $request->user();

        $plan = $tenant?->subscription?->plan;

        if (! $plan?->has_api_access) {
            return response()->json([
                'error' => 'API access requires Business or Enterprise plan',
            ], 403);
        }

        return $next($request);
    }
}
