<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class AnswerPracticeExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'answer' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'answer.required' => 'Debes ingresar o seleccionar una respuesta.',
        ];
    }
}
