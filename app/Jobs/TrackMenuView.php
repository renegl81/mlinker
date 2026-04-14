<?php

namespace App\Jobs;

use App\Models\MenuView;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;

class TrackMenuView implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $menuId,
        public readonly string $tenantId,
        public readonly ?string $ip,
        public readonly ?string $userAgent,
        public readonly ?string $referer,
    ) {}

    public function handle(): void
    {
        $deduplicationWindow = Carbon::now()->subMinutes(30);

        $exists = MenuView::withoutGlobalScopes()
            ->where('menu_id', $this->menuId)
            ->where('ip', $this->ip)
            ->where('viewed_at', '>=', $deduplicationWindow)
            ->exists();

        if ($exists) {
            return;
        }

        MenuView::withoutGlobalScopes()->create([
            'menu_id' => $this->menuId,
            'tenant_id' => $this->tenantId,
            'viewed_at' => Carbon::now(),
            'ip' => $this->ip,
            'user_agent' => $this->userAgent,
            'referer' => $this->referer,
        ]);
    }
}
