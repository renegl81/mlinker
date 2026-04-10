<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\MenuActivated;
use App\Mail\MenuPublishedMail;
use App\Models\Menu;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendMenuPublishedMail
{
    public function handle(MenuActivated $event): void
    {
        $menu = $event->menu;

        $tenant = $menu->tenant ?? null;

        if (! $tenant) {
            return;
        }

        $owner = $tenant->users()->first();

        if (! $owner) {
            return;
        }

        $publicUrl = $this->buildPublicMenuUrl($menu, $tenant);

        try {
            Mail::to($owner)->send(new MenuPublishedMail($owner, $menu, $publicUrl));
        } catch (\Throwable $e) {
            Log::error('MenuPublishedMail failed', [
                'menu_id' => $menu->id,
                'user_id' => $owner->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function buildPublicMenuUrl(Menu $menu, Tenant $tenant): string
    {
        $domain = $tenant?->domains()?->first()?->domain;

        if ($domain) {
            $scheme = parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'http';

            return "{$scheme}://{$domain}/m/{$menu->id}";
        }

        return route('tenant_public.tenant_menu_short', ['menu' => $menu->id]);
    }
}
