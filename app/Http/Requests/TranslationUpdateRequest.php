<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TranslationUpdateRequest extends FormRequest
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
            'translatable_id' => ['required', 'integer', 'exists:translatables,id'],
            'translatable_type' => ['required', 'string', 'max:100'],
            'locale' => ['required', 'string', 'max:10'],
            'field' => ['required', 'string', 'max:50'],
            'value' => ['required', 'string', 'max:255'],
        ];
    }
}
