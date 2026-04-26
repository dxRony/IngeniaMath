<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class StartFreePracticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'topic_id' => ['required', 'exists:topics,id'],
            'subtopic_id' => ['nullable', 'exists:subtopics,id'],
            'level_id' => ['required', 'exists:exercise_levels,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'topic_id.required' => 'Debe seleccionar un módulo.',
            'topic_id.exists' => 'El módulo seleccionado no es válido.',

            'subtopic_id.exists' => 'El subtema seleccionado no es válido.',

            'level_id.required' => 'Debe seleccionar un nivel de dificultad.',
            'level_id.exists' => 'El nivel seleccionado no es válido.',

            'quantity.required' => 'Debe indicar la cantidad de ejercicios.',
            'quantity.integer' => 'La cantidad debe ser un número entero.',
            'quantity.min' => 'Debe seleccionar al menos 1 ejercicio.',
            'quantity.max' => 'No puede seleccionar más de 20 ejercicios.',
        ];
    }
}
