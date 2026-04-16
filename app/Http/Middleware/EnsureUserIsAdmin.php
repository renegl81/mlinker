<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->hasRole('Owner')) {
            return redirect(url('http://'.$request->user()?->tenants()?->first()?->domains()?->first()->domain.':'.config('services.app_port').'/panel'));
        }
        // Si el usuario no tiene el rol 'admin', denegar acceso
        if (! $request->user() || ! $request->user()->hasRole('Admin')) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
