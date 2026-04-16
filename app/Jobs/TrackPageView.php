<?php

namespace App\Jobs;

use App\Models\PageView;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;

class TrackPageView implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $tenantId,
        public readonly string $pageType,
        public readonly ?int $resourceId,
        public readonly string $event,
        public readonly ?string $ip,
        public readonly ?string $userAgent,
        public readonly ?string $referer,
    ) {}

    public function handle(): void
    {
        // Dedup: same IP + page_type + resource_id + event within 30 min
        $exists = PageView::withoutGlobalScopes()
            ->where('tenant_id', $this->tenantId)
            ->where('page_type', $this->pageType)
            ->where('resource_id', $this->resourceId)
            ->where('event', $this->event)
            ->where('ip', $this->ip)
            ->where('viewed_at', '>=', Carbon::now()->subMinutes(30))
            ->exists();

        if ($exists) {
            return;
        }

        PageView::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenantId,
            'page_type' => $this->pageType,
            'resource_id' => $this->resourceId,
            'event' => $this->event,
            'viewed_at' => Carbon::now(),
            'ip' => $this->ip,
            'user_agent' => $this->userAgent,
            'referer' => $this->referer,
            'device_type' => $this->detectDevice($this->userAgent),
        ]);
    }

    private function detectDevice(?string $ua): string
    {
        if (! $ua) {
            return 'desktop';
        }

        $ua = strtolower($ua);

        if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) {
            return 'tablet';
        }

        if (str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone')) {
            return 'mobile';
        }

        return 'desktop';
    }
}
