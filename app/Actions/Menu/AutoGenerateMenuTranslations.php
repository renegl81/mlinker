<?php

declare(strict_types=1);

namespace App\Actions\Menu;

use App\Models\Menu;
use App\Services\AutoTranslator;

class AutoGenerateMenuTranslations
{
    public function __construct(private readonly AutoTranslator $translator) {}

    /**
     * Auto-generate translations for any menu/section/product that has no entry
     * for the target locale. Existing translations are preserved.
     *
     * @return bool true if at least one new translation was created
     */
    public function execute(Menu $menu, string $targetLocale, string $sourceLocale = 'es'): bool
    {
        if ($targetLocale === $sourceLocale) {
            return false;
        }

        $menu->loadMissing(['sections.products.ingredients']);

        // Collect strings keyed by a unique reference, so we can route the
        // translated output back to the correct model/field after the batch.
        $items = [];

        if ($this->needsTranslation($menu->translations, $targetLocale, 'name') && $menu->name) {
            $items["menu:{$menu->id}:name"] = $menu->name;
        }
        if ($this->needsTranslation($menu->translations, $targetLocale, 'description') && $menu->description) {
            $items["menu:{$menu->id}:description"] = $menu->description;
        }

        foreach ($menu->sections as $section) {
            if ($this->needsTranslation($section->translations, $targetLocale, 'name') && $section->name) {
                $items["section:{$section->id}:name"] = $section->name;
            }
            if ($this->needsTranslation($section->translations, $targetLocale, 'description') && $section->description) {
                $items["section:{$section->id}:description"] = $section->description;
            }

            foreach ($section->products as $product) {
                if ($this->needsTranslation($product->translations, $targetLocale, 'name') && $product->name) {
                    $items["product:{$product->id}:name"] = $product->name;
                }
                if ($this->needsTranslation($product->translations, $targetLocale, 'description') && $product->description) {
                    $items["product:{$product->id}:description"] = $product->description;
                }

                foreach ($product->ingredients as $ingredient) {
                    if ($this->needsTranslation($ingredient->translations, $targetLocale, 'name') && $ingredient->name) {
                        $items["ingredient:{$ingredient->id}:name"] = $ingredient->name;
                    }
                }
            }
        }

        if (empty($items)) {
            return false;
        }

        $translated = $this->translator->translateBatch($items, $sourceLocale, $targetLocale);

        if (empty($translated)) {
            return false;
        }

        // Bucket results by model id.
        $byModel = [];
        foreach ($translated as $key => $value) {
            [$type, $id, $field] = explode(':', $key);
            $byModel[$type][(int) $id][$field] = $value;
        }

        // Persist menu translations.
        if (! empty($byModel['menu'][$menu->id])) {
            $translations = $menu->translations ?? [];
            $translations[$targetLocale] = array_merge(
                $translations[$targetLocale] ?? [],
                $byModel['menu'][$menu->id],
            );
            $menu->update(['translations' => $translations]);
        }

        // Persist section translations.
        foreach ($menu->sections as $section) {
            if (empty($byModel['section'][$section->id])) {
                continue;
            }
            $translations = $section->translations ?? [];
            $translations[$targetLocale] = array_merge(
                $translations[$targetLocale] ?? [],
                $byModel['section'][$section->id],
            );
            $section->update(['translations' => $translations]);
        }

        // Persist product translations.
        foreach ($menu->sections as $section) {
            foreach ($section->products as $product) {
                if (empty($byModel['product'][$product->id])) {
                    continue;
                }
                $translations = $product->translations ?? [];
                $translations[$targetLocale] = array_merge(
                    $translations[$targetLocale] ?? [],
                    $byModel['product'][$product->id],
                );
                $product->update(['translations' => $translations]);
            }
        }

        // Persist ingredient translations.
        foreach ($menu->sections as $section) {
            foreach ($section->products as $product) {
                foreach ($product->ingredients as $ingredient) {
                    if (empty($byModel['ingredient'][$ingredient->id])) {
                        continue;
                    }
                    $translations = $ingredient->translations ?? [];
                    $translations[$targetLocale] = array_merge(
                        $translations[$targetLocale] ?? [],
                        $byModel['ingredient'][$ingredient->id],
                    );
                    $ingredient->update(['translations' => $translations]);
                }
            }
        }

        return true;
    }

    private function needsTranslation(?array $translations, string $locale, string $field): bool
    {
        $value = $translations[$locale][$field] ?? null;

        return ! is_string($value) || trim($value) === '';
    }
}
