<?php

namespace App\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;

class MenuStoreRequest extends FormRequest
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
