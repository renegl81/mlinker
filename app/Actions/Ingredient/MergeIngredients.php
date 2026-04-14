<?php

declare(strict_types=1);

namespace App\Actions\Ingredient;

use App\Models\Ingredient;
use Illuminate\Support\Facades\DB;

/**
 * Merge multiple ingredients of the current tenant into a single survivor.
 *
 * - Re-assigns every row in ingredient_product from the deleted ingredients
 *   to the survivor (using insertOrIgnore to avoid unique collisions).
 * - Deep-merges the translations JSON; survivor wins on conflicts.
 * - Deletes the absorbed ingredients.
 *
 * All steps run inside a transaction. All models must belong to the current
 * tenant — enforced via BelongsToTenant scope on Ingredient::query().
 */
class MergeIngredients
{
    /**
     * @param  array<int, int>  $ingredientIds  ids of ingredients to merge (must include survivor)
     */
    public function execute(array $ingredientIds, int $survivorId): Ingredient
    {
        $ingredientIds = array_values(array_unique(array_map('intval', $ingredientIds)));

        if (! in_array($survivorId, $ingredientIds, true)) {
            throw new \InvalidArgumentException('Survivor id must be in the list of ingredients to merge.');
        }

        if (count($ingredientIds) < 2) {
            throw new \InvalidArgumentException('Merging requires at least two ingredients.');
        }

        return DB::transaction(function () use ($ingredientIds, $survivorId): Ingredient {
            $absorbedIds = array_values(array_filter($ingredientIds, fn ($id) => $id !== $survivorId));

            $survivor = Ingredient::find($survivorId);
            if (! $survivor) {
                throw new \RuntimeException('Survivor ingredient not found in current tenant.');
            }

            $absorbed = Ingredient::whereIn('id', $absorbedIds)->get();
            if ($absorbed->count() !== count($absorbedIds)) {
                throw new \RuntimeException('One or more ingredients not found in current tenant.');
            }

            // 1) Deep-merge translations. Survivor wins on field-level conflicts.
            $mergedTranslations = $survivor->translations ?? [];
            foreach ($absorbed as $ingredient) {
                foreach (($ingredient->translations ?? []) as $locale => $fields) {
                    $mergedTranslations[$locale] = array_merge(
                        (array) ($fields ?? []),
                        (array) ($mergedTranslations[$locale] ?? []),
                    );
                }
            }
            if (! empty($mergedTranslations)) {
                $survivor->update(['translations' => $mergedTranslations]);
            }

            // 2) Reassign pivot rows from absorbed → survivor.
            $pivotRows = DB::table('ingredient_product')
                ->whereIn('ingredient_id', $absorbedIds)
                ->get();

            foreach ($pivotRows as $row) {
                DB::table('ingredient_product')->insertOrIgnore([
                    'ingredient_id' => $survivor->id,
                    'product_id' => $row->product_id,
                    'tenant_id' => $row->tenant_id,
                ]);
            }

            DB::table('ingredient_product')
                ->whereIn('ingredient_id', $absorbedIds)
                ->delete();

            // 3) Delete absorbed ingredients (iterate to bypass any query-builder quirks).
            foreach ($absorbed as $ingredient) {
                $ingredient->delete();
            }

            return $survivor->fresh();
        });
    }
}
