<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'location_id' => ['required', 'integer',  Rule::exists('locations', 'id')],
             'template_id' => ['required','integer', Rule::exists('templates', 'id')],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string'],
            'show_prices' => ['boolean'],
            'show_currency' => ['boolean'],
            'show_calories' => ['boolean'],
        ];
    }
}
