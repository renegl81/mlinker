<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;

class PatchProduct
{
    public function execute(Product $product, array $data): Product
    {
        $allowed = ['name', 'description', 'price', 'calories'];
        $updateData = array_intersect_key($data, array_flip($allowed));

        if (! empty($updateData)) {
            $product->update($updateData);
        }

        return $product->fresh();
    }
}
