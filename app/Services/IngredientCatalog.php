<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Ingredient;
use Illuminate\Support\Collection;
use Stancl\Tenancy\Database\TenantScope;

/**
 * Provides cross-tenant ingredient reuse with private copies.
 *
 * The catalog lets users discover ingredients that other tenants have already
 * created (and potentially translated), and import them into their own tenant
 * as a fresh row. No shared ownership — each tenant owns a copy with its own
 * translations column that can be edited freely without affecting anyone else.
 *
 * Popularity filter (usage across >=2 tenants) keeps tenant-specific one-off
 * names (e.g. "Mi salsa secreta") out of other tenants' suggestions.
 */
class IngredientCatalog
{
    private const POPULARITY_THRESHOLD = 2;
    private const DEFAULT_LIMIT = 500;

    /**
     * Returns ingredients available for import into the current tenant.
     *
     * - Cross-tenant ingredients whose normalized name is used by at least
     *   POPULARITY_THRESHOLD distinct tenants.
     * - Excludes ingredients whose name already exists in the current tenant
     *   (those appear in the tenant's own list instead).
     *
     * @return Collection<int, array{name: string, has_translations: bool, popularity: int}>
     */
    public function popular(int $limit = self::DEFAULT_LIMIT): Collection
    {
        $tenantId = tenant('id');

        $ownNamesLower = Ingredient::query()
            ->where('tenant_id', $tenantId)
            ->pluck('name')
            ->map(fn (string $n) => mb_strtolower($n))
            ->all();

        $rows = Ingredient::query()
            ->withoutGlobalScope(TenantScope::class)
            ->selectRaw('MIN(name) AS name, LOWER(name) AS normalized, COUNT(DISTINCT tenant_id) AS popularity, BOOL_OR(translations IS NOT NULL) AS has_translations')
            ->where('tenant_id', '!=', $tenantId)
            ->groupBy('normalized')
            ->havingRaw('COUNT(DISTINCT tenant_id) >= ?', [self::POPULARITY_THRESHOLD])
            ->orderByDesc('popularity')
            ->orderBy('normalized')
            ->limit($limit)
            ->get();

        return $rows
            ->reject(fn ($row) => in_array($row->normalized, $ownNamesLower, true))
            ->map(fn ($row) => [
                'name' => $row->name,
                'has_translations' => (bool) $row->has_translations,
                'popularity' => (int) $row->popularity,
            ])
            ->values();
    }

    /**
     * Find an ingredient in the current tenant by name, or create a new one.
     *
     * If another tenant already has an ingredient with the same (case-insensitive)
     * name and it carries translations, those translations are copied into the
     * new local row so the current tenant benefits from prior work. Subsequent
     * edits affect only the local copy.
     */
    public function findOrImport(string $name): Ingredient
    {
        $name = trim($name);
        $tenantId = tenant('id');

        $local = Ingredient::where('tenant_id', $tenantId)
            ->where('name', $name)
            ->first();

        if ($local) {
            return $local;
        }

        $donor = Ingredient::query()
            ->withoutGlobalScope(TenantScope::class)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->where('tenant_id', '!=', $tenantId)
            ->whereNotNull('translations')
            ->oldest('id')
            ->first();

        return Ingredient::create([
            'name' => $name,
            'translations' => $donor?->translations,
        ]);
    }
}
