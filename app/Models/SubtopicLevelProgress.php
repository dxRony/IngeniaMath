<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubtopicLevelProgress extends Model
{
    protected $table = 'subtopic_level_progress';


    protected $fillable = [
        'student_id',
        'subtopic_id',
        'current_difficulty_id',
        'consecutive_correct',
        'consecutive_incorrect',
        'total_exercises',
        'total_correct',
        'accuracy_percentage',
    ];

    protected function casts(): array
    {
        return [
            'consecutive_correct' => 'integer',
            'consecutive_incorrect' => 'integer',
            'total_exercises' => 'integer',
            'total_correct' => 'integer',
            'accuracy_percentage' => 'float',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id', 'id');
    }

    public function subtopic(): BelongsTo
    {
        return $this->belongsTo(Subtopic::class, 'subtopic_id', 'id');
    }

    public function currentDifficulty(): BelongsTo
    {
        return $this->belongsTo(ExerciseLevel::class, 'current_difficulty_id', 'id');
    }
}
