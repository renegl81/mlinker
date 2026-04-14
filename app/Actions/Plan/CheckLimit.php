<?php

namespace App\Actions\Plan;

use App\Exceptions\PlanLimitExceededException;
use App\Models\Location;
use App\Models\Menu;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;

class CheckLimit
{
    /**
     * Check whether the current tenant can create a new resource.
     *
     * @param  string  $resource  One of: locations, menus, products, images
     * @param  bool  $throw  If true, throws PlanLimitExceededException on limit exceeded
     * @return bool True if the tenant is within limits, false otherwise
     *
     * @throws PlanLimitExceededException
     */
    public function execute(string $resource, bool $throw = false): bool
    {
        $tenantId = tenant()->id;

        $plan = $this->resolvePlan($tenantId);

        // No plan found — treat as unlimited
        if ($plan === null) {
            return true;
        }

        $maxKey = $this->resolveMaxKey($resource);
        $max = (int) ($plan->{$maxKey} ?? 0);

        // 0 means unlimited
        if ($max === 0) {
            return true;
        }

        $current = $this->countResource($resource, $tenantId);

        if ($current >= $max) {
            if ($throw) {
                throw new PlanLimitExceededException($resource, $max);
            }

            return false;
        }

        return true;
    }

    /**
     * Map resource names to their corresponding plan column.
     * Uses max_menus_per_location for total-menu enforcement since
     * there is no separate max_menus column.
     */
    private function resolveMaxKey(string $resource): string
    {
        return match ($resource) {
            'menus' => 'max_menus_per_location',
            default => "max_{$resource}",
        };
    }

    private function resolvePlan(string $tenantId): ?Plan
    {
        $subscription = Subscription::where('tenant_id', $tenantId)
            ->latest()
            ->with('plan')
            ->first();

        return $subscription?->plan ?? Plan::free();
    }

    private function countResource(string $resource, string $tenantId): int
    {
        return match ($resource) {
            'locations' => Location::where('tenant_id', $tenantId)->count(),
            'menus' => Menu::where('tenant_id', $tenantId)->count(),
            'products' => Product::where('tenant_id', $tenantId)->count(),
            'images' => Product::where('tenant_id', $tenantId)
                ->whereNotNull('image_url')
                ->count(),
            default => 0,
        };
    }
}
