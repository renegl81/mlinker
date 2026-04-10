<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Ingredient;
use App\Models\Product;
use App\Support\ImageHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpdateProduct
{
    public function execute(Product $product, array $data): Product
    {
        $productData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'calories' => $data['calories'] ?? null,
            'tags' => $data['tags'] ?? null,
        ];

        // Image handling (same pattern as UpdateMenu):
        // - base64  → store new, delete old
        // - null    → remove image
        // - http(s) → already resolved URL, do not touch
        // - key not present → do not touch
        if (array_key_exists('image_url', $data)) {
            $incoming = $data['image_url'];
            if (is_string($incoming) && str_starts_with($incoming, 'data:image')) {
                if ($product->image_url) {
                    Storage::disk('public')->delete($product->image_url);
                }
                $productData['image_url'] = ImageHelper::storeBase64Image($incoming, 'products');
            } elseif ($incoming === null) {
                if ($product->image_url) {
                    Storage::disk('public')->delete($product->image_url);
                }
                $productData['image_url'] = null;
            }
            // http(s) URL → user did not change the image
        }

        $product->update($productData);

        // Sync section: detach all, attach the new one
        if (array_key_exists('section_id', $data)) {
            DB::table('product_section')->where('product_id', $product->id)->delete();
            if (! empty($data['section_id'])) {
                DB::table('product_section')->insert([
                    'product_id' => $product->id,
                    'section_id' => $data['section_id'],
                    'tenant_id' => tenant('id'),
                ]);
            }
        }

        // Sync allergens
        DB::table('allergen_product')->where('product_id', $product->id)->delete();
        if (! empty($data['allergen_ids'])) {
            $rows = array_map(fn ($allergenId) => [
                'allergen_id' => $allergenId,
                'product_id' => $product->id,
                'tenant_id' => tenant('id'),
            ], $data['allergen_ids']);
            DB::table('allergen_product')->insert($rows);
        }

        // Sync ingredients (by name — create if not exists)
        DB::table('ingredient_product')->where('product_id', $product->id)->delete();
        if (! empty($data['ingredient_names'])) {
            foreach ($data['ingredient_names'] as $name) {
                $name = trim($name);
                if ($name === '') {
                    continue;
                }
                $ingredient = Ingredient::firstOrCreate(
                    ['name' => $name, 'tenant_id' => tenant('id')],
                );
                DB::table('ingredient_product')->insertOrIgnore([
                    'ingredient_id' => $ingredient->id,
                    'product_id' => $product->id,
                    'tenant_id' => tenant('id'),
                ]);
            }
        }

        return $product->fresh(['allergens', 'ingredients', 'sections']);
    }
}
