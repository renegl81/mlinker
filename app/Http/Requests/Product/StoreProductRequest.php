<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'calories' => ['nullable', 'numeric', 'min:0'],
            'image_url' => ['nullable', 'string'],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
            'allergen_ids' => ['nullable', 'array'],
            'allergen_ids.*' => ['integer', 'exists:allergens,id'],
            'ingredient_names' => ['nullable', 'array'],
            'ingredient_names.*' => ['string', 'max:100'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'in:vegetarian,vegan,spicy,gluten_free'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del producto es obligatorio.',
            'name.max' => 'El nombre no puede superar los 100 caracteres.',
            'price.required' => 'El precio es obligatorio.',
            'price.numeric' => 'El precio debe ser un número válido.',
            'price.min' => 'El precio debe ser mayor o igual a 0.',
            'price.max' => 'El precio no puede superar 999999.99.',
            'calories.numeric' => 'Las calorías deben ser un número válido.',
            'calories.min' => 'Las calorías deben ser mayor o igual a 0.',
            'section_id.required' => 'Debes seleccionar una sección.',
            'section_id.exists' => 'La sección seleccionada no es válida.',
            'allergen_ids.array' => 'Los alérgenos deben ser un array.',
            'allergen_ids.*.exists' => 'Uno de los alérgenos seleccionados no es válido.',
        ];
    }
}
