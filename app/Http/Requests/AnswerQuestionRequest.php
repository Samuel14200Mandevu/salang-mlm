<?php

namespace App\Http\Requests;

use App\Models\QuestionConversation;
use Illuminate\Foundation\Http\FormRequest;

class AnswerQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $conversation = $this->route('question');

        return $conversation instanceof QuestionConversation
            && $this->user()?->can('answer', $conversation);
    }

    public function rules(): array
    {
        return [
            'answer' => ['required', 'string', 'min:5'],
        ];
    }
}
