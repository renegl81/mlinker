<?php

namespace App\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'location_id' => ['required', 'integer',  Rule::exists('locations', 'id')],
            'template_id' => ['required', 'integer',  Rule::exists('templates', 'id')],
            'show_currency' => ['boolean'],
            'show_prices' => ['boolean'],
            'show_calories' => ['boolean'],
            'image_url' => ['nullable', 'string', 'regex:/^data:image\/(jpeg|jpg|png|gif);base64,/'],
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
