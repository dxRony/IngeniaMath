<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionTopicSummary extends Model
{
    protected $table = 'session_topic_summaries';

    protected $fillable = [
        'session_id',
        'topic_id',
        'total_exercises',
        'total_correct',
        'total_incorrect',
    ];

    protected function casts(): array
    {
        return [
            'total_exercises' => 'integer',
            'total_correct' => 'integer',
            'total_incorrect' => 'integer',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(PracticeSession::class, 'session_id', 'id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'topic_id', 'id');
    }
}
