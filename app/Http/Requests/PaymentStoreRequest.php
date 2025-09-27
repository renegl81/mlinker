<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentStoreRequest extends FormRequest
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
            'subscription_id' => ['required', 'integer', 'exists:Subscriptions,id'],
            'amount' => ['required', 'numeric', 'between:-999999.99,999999.99'],
            'paid_at' => ['required'],
            'payment_method' => ['required', 'string', 'max:30'],
            'status' => ['required', 'string', 'max:20'],
        ];
    }
}
