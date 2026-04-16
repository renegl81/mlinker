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
            'order_email' => ['nullable', 'email', 'max:255'],
            'order_whatsapp' => ['nullable', 'string', 'max:20'],
            'is_pet_friendly' => ['nullable', 'boolean'],
            'has_wifi' => ['nullable', 'boolean'],
            'has_terrace' => ['nullable', 'boolean'],
            'has_parking' => ['nullable', 'boolean'],
            'is_accessible' => ['nullable', 'boolean'],
            'reservation_url' => ['nullable', 'url', 'max:500'],
            'reservation_phone' => ['nullable', 'string', 'max:20'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'google_maps_url' => ['nullable', 'url', 'max:500'],
            'opening_hours' => ['nullable', 'array'],
            'opening_hours.*.weekday' => ['required', 'integer', 'between:0,6'],
            'opening_hours.*.opens_at' => ['nullable', 'string'],
            'opening_hours.*.closes_at' => ['nullable', 'string'],
            'opening_hours.*.is_closed' => ['nullable', 'boolean'],
        ];
    }
}
