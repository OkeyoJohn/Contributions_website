<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDonationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /**
     * The frontend validates the phone with spaces stripped (e.g. "0712 345 678")
     * but sends the value as typed. Strip whitespace here too so valid numbers
     * are not rejected with a 422 before M-Pesa's normalizePhone() can clean them.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('phone'))) {
            $this->merge(['phone' => preg_replace('/\s+/', '', $this->input('phone'))]);
        }
    }

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
