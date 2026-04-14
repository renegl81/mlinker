<?php

namespace App\Actions\Menu;

use App\Models\Location;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;

class CloneMenuToLocation
{
    public function execute(Menu $menu, Location $targetLocation): Menu
    {
        return DB::transaction(function () use ($menu, $targetLocation) {
            $menu->load(['sections.products.allergens', 'sections.products.ingredients']);

            // Clone the menu
            $newMenu = $menu->replicate(['id', 'created_at', 'updated_at']);
            $newMenu->location_id = $targetLocation->id;
            $newMenu->name = $menu->name;
            $newMenu->customization = $menu->customization;
            $newMenu->save();

            // Clone sections and products
            foreach ($menu->sections as $section) {
                $newSection = $section->replicate(['id', 'created_at', 'updated_at']);
                $newSection->menu_id = $newMenu->id;
                $newSection->save();

                foreach ($section->products as $product) {
                    $newProduct = $product->replicate(['id', 'created_at', 'updated_at']);
                    $newProduct->save();

                    DB::table('product_section')->insert([
                        'product_id' => $newProduct->id,
                        'section_id' => $newSection->id,
                        'tenant_id' => $newSection->tenant_id ?? tenant()->id,
                    ]);

                    if ($product->allergens->isNotEmpty()) {
                        foreach ($product->allergens as $allergen) {
                            DB::table('allergen_product')->insert([
                                'allergen_id' => $allergen->id,
                                'product_id' => $newProduct->id,
                                'tenant_id' => tenant('id'),
                            ]);
                        }
                    }

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
