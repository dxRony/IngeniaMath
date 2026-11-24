<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MockExamExercise extends Model
{
    protected $table = 'mock_exam_exercises';


    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = [
        'exam_id',
        'exercise_id',
        'order',
        'given_answer',
        'resolution_seconds',
        'answered_at',
        'is_correct',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'resolution_seconds' => 'integer',
            'answered_at' => 'datetime',
            'is_correct' => 'boolean',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(MockExam::class, 'exam_id', 'id');
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class, 'exercise_id', 'id');
    }
}
