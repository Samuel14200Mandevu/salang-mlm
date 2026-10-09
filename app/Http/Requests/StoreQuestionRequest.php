<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject.required' => 'Le sujet est obligatoire.',
            'body.required' => 'Le message est obligatoire.',
            'body.min' => 'Le message doit contenir au moins 10 caractères.',
        ];
    }
}
