<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        $uiLocales = ['es', 'en', 'ca', 'gl', 'eu'];

        // 1. URL prefix has highest priority (public pages)
        $urlLocale = $request->route('locale');
        if ($urlLocale && in_array($urlLocale, $uiLocales)) {
            app()->setLocale($urlLocale);

            return $next($request);
        }

        // 2. Authenticated user's stored locale
        $user = $request->user();
        if ($user && ! empty($user->locale) && in_array($user->locale, $uiLocales)) {
            app()->setLocale($user->locale);

            return $next($request);
        }

        // 3. Default
        app()->setLocale(config('menulinker.source_locale', 'es'));

        return $next($request);
    }
}
