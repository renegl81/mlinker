<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use App\Models\Location;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in(['owner', 'editor'])],
            'location_scope' => ['required_if:role,editor', Rule::in(['all', 'locations'])],
            'location_ids' => ['required_if:location_scope,locations', 'array'],
            'location_ids.*' => ['integer', Rule::exists('locations', 'id')],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $ids = $this->input('location_ids', []);
            if (empty($ids)) {
                return;
            }
            $valid = Location::whereIn('id', $ids)->pluck('id')->all();
            $invalid = array_diff($ids, $valid);
            if (! empty($invalid)) {
                $validator->errors()->add('location_ids', 'Una o más locations no pertenecen a este tenant.');
            }
        });
    }
}
