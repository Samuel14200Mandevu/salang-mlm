<?php
// app/Http/Requests/WithdrawalRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'amount' => [
                'required',
                'numeric',
                'min:10',
                'max:10000',
            ],
            'method' => ['required', 'in:crypto,mobile_money,bank_transfer'],
            'address' => ['required_if:method,crypto', 'string'],
            'phone' => ['required_if:method,mobile_money', 'string'],
            'bank' => ['required_if:method,bank_transfer', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Le montant est obligatoire.',
            'amount.min' => 'Le montant minimum est de 10 USD.',
            'amount.max' => 'Le montant maximum est de 10,000 USD.',
            'method.required' => 'La méthode de retrait est obligatoire.',
            'address.required_if' => 'L\'adresse crypto est obligatoire.',
            'phone.required_if' => 'Le numéro de téléphone est obligatoire.',
        ];
    }
}