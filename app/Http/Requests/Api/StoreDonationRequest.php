<?php

namespace App\Http\Requests\Api;

use App\Models\Donation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDonationRequest extends FormRequest
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
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => [
                'nullable',
                'required_if:payment_method,mpesa',
                'regex:/^(?:\+?254|0)?[71]\d{8}$/',
            ],
            'amount' => ['required', 'integer', 'min:50', 'max:100000000'],
            'currency' => ['nullable', 'string', 'size:3', 'alpha'],
            'campaign_id' => ['nullable', 'integer', Rule::exists('campaigns', 'id')->where('is_active', true)],
            'anonymous' => ['sometimes', 'boolean'],
            'message' => ['nullable', 'string', 'max:300'],
            'payment_method' => ['required', Rule::in(Donation::PAYMENT_METHODS)],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.min' => 'The minimum donation is KES 50.',
            'amount.integer' => 'Enter a whole number of shillings.',
            'phone.required_if' => 'Enter your M-Pesa phone number.',
            'phone.regex' => 'Enter a Kenyan number such as 0712 345 678.',
            'campaign_id.exists' => 'Choose a campaign from the list.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'amount' => is_numeric($this->input('amount')) ? (int) $this->input('amount') : $this->input('amount'),
            'anonymous' => (bool) $this->input('anonymous', false),
            'currency' => $this->input('currency') ? strtoupper((string) $this->input('currency')) : 'KES',
            'campaign_id' => $this->input('campaign_id') ?: null,
            'phone' => $this->input('phone') === null
                ? null
                : preg_replace('/[\s\-()]/', '', (string) $this->input('phone')),
        ]);
    }
}