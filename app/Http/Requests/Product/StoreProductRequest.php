<?php

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
            'calories' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'image' => ['nullable', 'image', 'max:2048'],
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
            'image.image' => 'El archivo debe ser una imagen.',
            'image.max' => 'La imagen no puede superar los 2MB.',
        ];
    }
}
