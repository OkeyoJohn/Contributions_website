<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDonationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $mpesa = $this->input('payment_method') === 'mpesa';
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => $mpesa
                ? ['required', 'regex:/^(?:\+?254|0)?[71]\d{8}$/']
                : ['nullable', 'string', 'max:20'],
            'amount' => ['required', 'numeric', 'min:50', 'max:1000000'],
            'currency' => ['nullable', 'in:KES'],
            'campaign_id' => ['nullable', 'integer', 'exists:campaigns,id'],
            'anonymous' => ['nullable', 'boolean'],
            'message' => ['nullable', 'string', 'max:300'],
            'payment_method' => ['required', 'in:mpesa,paystack,paypal'],
        ];
    }

    public function messages(): array
    {
        return ['phone.regex' => 'Enter a valid Kenyan number such as 0712 345 678.'];
    }
}
