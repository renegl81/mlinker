<?php

declare(strict_types=1);

namespace App\Actions\Menu;

use App\Models\Menu;
use Illuminate\Support\Facades\DB;

class DuplicateMenu
{
    public function execute(Menu $menu): Menu
    {
        return DB::transaction(function () use ($menu) {
            $menu->load(['sections.products.allergens', 'sections.products.ingredients']);

            // 1. Duplicate the menu
            $newMenu = $menu->replicate(['id', 'created_at', 'updated_at']);
            $newMenu->name = $menu->name.' (copia)';
            $newMenu->customization = $menu->customization;
            $newMenu->save();

            // 2. Duplicate sections and their products
            foreach ($menu->sections as $section) {
                $newSection = $section->replicate(['id', 'created_at', 'updated_at']);
                $newSection->menu_id = $newMenu->id;
                $newSection->save();

                foreach ($section->products as $product) {
                    // Duplicate the product
                    $newProduct = $product->replicate(['id', 'created_at', 'updated_at']);
                    $newProduct->save();

                    // Attach to the new section
                    DB::table('product_section')->insert([
                        'product_id' => $newProduct->id,
                        'section_id' => $newSection->id,
                        'tenant_id' => $newSection->tenant_id ?? tenant()->id,
                    ]);

                    // Copy allergen associations
                    if ($product->allergens->isNotEmpty()) {
                        foreach ($product->allergens as $allergen) {
                            DB::table('allergen_product')->insert([
                                'allergen_id' => $allergen->id,
                                'product_id' => $newProduct->id,
                                'tenant_id' => tenant('id'),
                            ]);
                        }
                    }

                    // Copy ingredient associations
                    if ($product->ingredients->isNotEmpty()) {
                        foreach ($product->ingredients as $ingredient) {
                            DB::table('ingredient_product')->insert([
                                'ingredient_id' => $ingredient->id,
                                'product_id' => $newProduct->id,
                                'tenant_id' => tenant('id'),
                            ]);
                        }
                    }
                }
            }

            return $newMenu;
        });
    }
}
