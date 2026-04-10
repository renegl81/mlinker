<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

class UpdateProductRequest extends StoreProductRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        // section_id is optional on update (we always include it but relax it)
        $rules['section_id'] = ['nullable', 'integer', 'exists:sections,id'];

        return $rules;
    }
}
