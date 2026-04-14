<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Menu;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class DuplicateProduct
{
    public function execute(Product $product): Product
    {
        $product->load(['sections', 'allergens', 'ingredients']);

        // Clone the product
        $clone = $product->replicate(['id', 'created_at', 'updated_at']);
        $clone->name = $product->name.' (copia)';
        $clone->save();

        // Copy section pivots
        foreach ($product->sections as $section) {
            DB::table('product_section')->insert([
                'product_id' => $clone->id,
                'section_id' => $section->id,
                'tenant_id' => tenant('id'),
            ]);

            // Also attach to the same menus via menu_product
            $menus = Menu::whereHas('sections', fn ($q) => $q->where('sections.id', $section->id))->get();
            foreach ($menus as $menu) {
                $alreadyAttached = DB::table('menu_product')
                    ->where('menu_id', $menu->id)
                    ->where('product_id', $clone->id)
                    ->exists();
                if (! $alreadyAttached) {
                    $maxSortOrder = DB::table('menu_product')
                        ->where('menu_id', $menu->id)
                        ->max('sort_order') ?? 0;
                    DB::table('menu_product')->insert([
                        'menu_id' => $menu->id,
                        'product_id' => $clone->id,
                        'tenant_id' => tenant('id'),
                        'sort_order' => $maxSortOrder + 1,
                    ]);
                }
            }
        }

        // Copy allergen pivots
        foreach ($product->allergens as $allergen) {
            DB::table('allergen_product')->insert([
                'allergen_id' => $allergen->id,
                'product_id' => $clone->id,
                'tenant_id' => tenant('id'),
            ]);
        }

        // Copy ingredient pivots
        foreach ($product->ingredients as $ingredient) {
            DB::table('ingredient_product')->insert([
                'ingredient_id' => $ingredient->id,
                'product_id' => $clone->id,
                'tenant_id' => tenant('id'),
            ]);
        }

        return $clone;
    }
}
