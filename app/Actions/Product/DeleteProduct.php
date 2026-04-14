<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteProduct
{
    public function execute(Product $product): void
    {
        // Remove from all pivots
        DB::table('product_section')->where('product_id', $product->id)->delete();
        DB::table('menu_product')->where('product_id', $product->id)->delete();
        DB::table('allergen_product')->where('product_id', $product->id)->delete();
        DB::table('ingredient_product')->where('product_id', $product->id)->delete();

        // Remove image from storage
        if ($product->image_url && ! str_starts_with($product->image_url, 'http')) {
            Storage::disk('public')->delete($product->image_url);
        }

        $product->delete();
    }
}
