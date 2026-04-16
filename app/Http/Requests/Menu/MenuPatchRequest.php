<?php

namespace App\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;

class MenuPatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
            'template_id' => ['sometimes', 'nullable', 'integer', 'exists:templates,id'],
            'show_prices' => ['sometimes', 'boolean'],
            'show_currency' => ['sometimes', 'boolean'],
            'show_calories' => ['sometimes', 'boolean'],
            'lang' => ['sometimes', 'string', 'max:5'],
        ];
    }
}
