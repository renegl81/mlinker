<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyFromSanctum
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Tenant|null $tenant */
        $tenant = $request->user();

        if ($tenant) {
            tenancy()->initialize($tenant->getKey());
        }

        return $next($request);
    }
}
