<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class RegisterRequest extends FormRequest
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
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'tenant_name' => ['required', 'string', 'max:255'],
            'tenant_domain' => ['required', 'string', 'lowercase', 'max:255', 'regex:/^[a-z0-9-]+$/', 'unique:domains,domain'],
            'locale' => ['sometimes', 'string', 'max:5'],
            'terms_accepted' => ['accepted'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('auth.register.validation.name_required'),
            'name.string' => __('auth.register.validation.name_string'),
            'name.max' => __('auth.register.validation.name_max'),
            'email.required' => __('auth.register.validation.email_required'),
            'email.email' => __('auth.register.validation.email_email'),
            'email.unique' => __('auth.register.validation.email_unique'),
            'password.required' => __('auth.register.validation.password_required'),
            'password.confirmed' => __('auth.register.validation.password_confirmed'),
            'tenant_name.required' => __('auth.register.validation.tenant_name_required'),
            'tenant_name.max' => __('auth.register.validation.tenant_name_max'),
            'tenant_domain.required' => __('auth.register.validation.tenant_domain_required'),
            'tenant_domain.lowercase' => __('auth.register.validation.tenant_domain_lowercase'),
            'tenant_domain.regex' => __('auth.register.validation.tenant_domain_regex'),
            'tenant_domain.unique' => __('auth.register.validation.tenant_domain_unique'),
            'terms_accepted.accepted' => __('auth.register.validation.terms_required'),
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('auth.register.name'),
            'email' => __('auth.register.email'),
            'password' => __('auth.register.password'),
            'password_confirmation' => __('auth.register.password_confirmation'),
            'tenant_name' => __('auth.register.tenant_name'),
            'tenant_domain' => __('auth.register.tenant_domain'),
            'terms_accepted' => __('auth.register.attributes.terms_accepted'),
        ];
    }
}
