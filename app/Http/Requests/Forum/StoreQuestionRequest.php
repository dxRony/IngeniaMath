<?php

namespace App\Http\Requests\Forum;

use App\Models\Thread;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $threadId = (int) $this->input('thread_id');
        $thread = Thread::find($threadId);

        return $thread !== null && (int) $thread->created_by === (int) $this->user()?->id;
    }

    public function rules(): array
    {
        return [
            'thread_id' => ['required', 'integer', 'exists:threads,id'],
            'body' => ['required', 'string', 'max:5000'],
            'resource_url' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
