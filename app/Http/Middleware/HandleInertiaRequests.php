<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => auth()->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'ziggy' => fn (): array => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'locale' => fn () => auth()->user()?->locale ?? app()->getLocale(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'messages' => fn () => App::environment('production')
                 ? cache()->rememberForever('messages.'.app()->getLocale(), fn () => __('messages'))
                 : __('messages'),
            'tenant' => fn () => $this->tenantFeatures(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function tenantFeatures(): ?array
    {
        if (! function_exists('tenant') || ! tenant()) {
            return null;
        }

        $subscription = \App\Models\Subscription::where('tenant_id', tenant()->id)
            ->latest()
            ->with('plan')
            ->first();

        $plan = $subscription?->plan ?? \App\Models\Plan::free();

        $user = auth()->user();

        return [
            'id' => tenant()->id,
            'plan_features' => [
                'multilang' => (bool) ($plan?->has_multilang ?? false),
                'catalog' => (bool) ($plan?->has_catalog ?? false),
                'team' => (bool) ($plan?->has_team ?? false),
                'analytics' => (bool) ($plan?->has_analytics ?? false),
                'custom_qr' => (bool) ($plan?->has_custom_qr ?? false),
                'api_access' => (bool) ($plan?->has_api_access ?? false),
            ],
            'user_role' => $user instanceof \App\Models\User ? $user->currentTenantRole() : null,
        ];
    }
}
