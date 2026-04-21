<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PracticeSession extends Model
{
    protected $table = 'practice_sessions';


    protected $fillable = [
        'student_id',
        'mode_id',
        'topic_id',
        'initial_difficulty_id',
        'started_at',
        'ended_at',
        'total_exercises',
        'total_correct',
        'total_incorrect',
        'total_time_seconds',
        'status_id',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'total_exercises' => 'integer',
            'total_correct' => 'integer',
            'total_incorrect' => 'integer',
            'total_time_seconds' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id', 'id');
    }

    public function mode(): BelongsTo
    {
        return $this->belongsTo(SessionMode::class, 'mode_id', 'id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'topic_id', 'id');
    }

    public function initialDifficulty(): BelongsTo
    {
        return $this->belongsTo(DifficultyLevel::class, 'initial_difficulty_id', 'id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(PracticeStatus::class, 'status_id', 'id');
    }

    public function sessionExercises(): HasMany
    {
        return $this->hasMany(SessionExercise::class, 'session_id', 'id');
    }

    public function recommendationHistories(): HasMany
    {
        return $this->hasMany(RecommendationHistory::class, 'session_id', 'id');
    }

    public function savedExercises(): HasMany
    {
        return $this->hasMany(SavedExercise::class, 'session_id', 'id');
    }

    public function sessionTopicSummaries(): HasMany
    {
        return $this->hasMany(SessionTopicSummary::class, 'session_id', 'id');
    }
}
