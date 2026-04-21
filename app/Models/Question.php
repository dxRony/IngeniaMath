<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $table = 'questions';


    protected $fillable = [
        'thread_id',
        'body',
        'resource_url',
        'order',
        'is_deleted',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'is_deleted' => 'boolean',
        ];
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class, 'thread_id', 'id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(QuestionReply::class, 'question_id', 'id');
    }
}
