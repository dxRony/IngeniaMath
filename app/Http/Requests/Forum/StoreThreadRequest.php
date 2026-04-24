<?php

namespace App\Http\Requests\Forum;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreThreadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'topic_id' => ['required', 'exists:topics,id'],
            'subtopic_id' => [
                'required',
                Rule::exists('subtopics', 'id')->where(fn ($query) => $query->where('topic_id', $this->input('topic_id'))),
            ],
            'initial_question' => ['required', 'string', 'max:2000'],
            'resource_url' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
