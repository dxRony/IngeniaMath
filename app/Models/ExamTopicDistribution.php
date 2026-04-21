<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamTopicDistribution extends Model
{
    protected $table = 'exam_topic_distributions';

    protected $fillable = [
        'config_id',
        'topic_id',
        'question_count',
    ];

    protected function casts(): array
    {
        return [
            'question_count' => 'integer',
        ];
    }

    public function config(): BelongsTo
    {
        return $this->belongsTo(ExamConfig::class, 'config_id', 'id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'topic_id', 'id');
    }
}
