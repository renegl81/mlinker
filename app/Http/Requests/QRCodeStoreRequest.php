<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QRCodeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parameters' => ['sometimes', 'array'],
            'parameters.size' => ['sometimes', 'integer', 'between:100,1200'],
            'parameters.margin' => ['sometimes', 'integer', 'between:0,80'],
            'parameters.foreground' => ['sometimes', 'string', 'regex:/^#?[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/'],
            'parameters.background' => ['sometimes', 'string', 'regex:/^#?[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/'],
            'parameters.label' => ['sometimes', 'nullable', 'string', 'max:60'],
        ];
    }
}
