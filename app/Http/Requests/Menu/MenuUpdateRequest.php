<?php

namespace App\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;

class MenuUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
            'template_id' => ['nullable', 'integer', 'exists:templates,id'],
            'show_prices' => ['boolean'],
            'show_currency' => ['boolean'],
            'show_calories' => ['boolean'],
            'image_url' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('messages.menus.validation.name_required'),
            'name.string' => __('messages.menus.validation.name_string'),
            'name.max' => __('messages.menus.validation.name_max'),
            'description.string' => __('messages.menus.validation.description_string'),
            'description.max' => __('messages.menus.validation.description_max'),
        ];
    }
}
