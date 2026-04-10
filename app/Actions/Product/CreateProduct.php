<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Product;
use App\Support\ImageHelper;
use Illuminate\Support\Facades\DB;

class CreateProduct
{
    public function execute(Menu $menu, array $data): Product
    {
        $productData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'calories' => $data['calories'] ?? null,
            'tags' => $data['tags'] ?? null,
            'tenant_id' => tenant('id'),
        ];

        // Handle image: base64 or file upload path
        if (isset($data['image_url']) && is_string($data['image_url'])) {
            if (str_starts_with($data['image_url'], 'data:image')) {
                $productData['image_url'] = ImageHelper::storeBase64Image($data['image_url'], 'products');
            } else {
                $productData['image_url'] = $data['image_url'];
            }
        }

        $product = Product::create($productData);

        // Attach to section via pivot (with tenant_id)
        if (! empty($data['section_id'])) {
            DB::table('product_section')->insert([
                'product_id' => $product->id,
                'section_id' => $data['section_id'],
                'tenant_id' => tenant('id'),
            ]);
        }

        // Attach to menu via pivot
        $maxSortOrder = DB::table('menu_product')
            ->where('menu_id', $menu->id)
            ->max('sort_order') ?? 0;

        DB::table('menu_product')->insert([
            'menu_id' => $menu->id,
            'product_id' => $product->id,
            'tenant_id' => tenant('id'),
            'sort_order' => $maxSortOrder + 1,
        ]);

        // Sync allergens
        if (! empty($data['allergen_ids'])) {
            $rows = array_map(fn ($allergenId) => [
                'allergen_id' => $allergenId,
                'product_id' => $product->id,
                'tenant_id' => tenant('id'),
            ], $data['allergen_ids']);
            DB::table('allergen_product')->insert($rows);
        }

        // Sync ingredients (by name — create if not exists)
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

        return $product->load(['allergens', 'ingredients', 'sections']);
    }
}
