<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LocationUpdateRequest extends FormRequest
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
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:255'],
            'country_id' => 'required|exists:countries,id',
            'phone' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'logo_url' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'string'],
            'languages' => ['nullable', 'array'],
            'currency' => ['nullable', 'string', 'max:3'],
            'time_zone' => ['nullable', 'string', 'max:30'],
            'time_format' => ['nullable', 'string', 'max:30'],
            'lang' => ['nullable', 'string', 'max:3'],
            'social_medias' => ['nullable', 'array'],
            'primary_color' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }
}
