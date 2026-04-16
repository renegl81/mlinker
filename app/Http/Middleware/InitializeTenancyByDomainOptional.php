<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

class InitializeTenancyByDomainOptional extends InitializeTenancyByDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            // Intentar inicializar el tenant
            return parent::handle($request, $next);
        } catch (TenantCouldNotBeIdentifiedOnDomainException $e) {
            // Si no se puede identificar el tenant, continuar sin inicializar
            // Esto permite que las rutas funcionen tanto en dominios de tenant
            // como en el dominio central
            return $next($request);
        }
    }
}
