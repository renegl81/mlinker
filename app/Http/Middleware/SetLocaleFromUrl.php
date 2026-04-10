<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');
        $uiLocales = ['es', 'en', 'ca', 'gl', 'eu'];

        if ($locale && in_array($locale, $uiLocales)) {
            app()->setLocale($locale);
        } else {
            app()->setLocale(config('menulinker.source_locale', 'es'));
        }

        return $next($request);
    }
}
