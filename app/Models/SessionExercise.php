<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionExercise extends Model
{
    protected $table = 'session_exercises';

    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'exercise_id',
        'order',
        'given_answer',
        'is_correct',
        'resolution_seconds',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'is_correct' => 'boolean',
            'resolution_seconds' => 'integer',
            'answered_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(PracticeSession::class, 'session_id', 'id');
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class, 'exercise_id', 'id');
    }

    public function savedExercises()
    {
        return $this->hasMany(SavedExercise::class, 'session_exercise_id', 'id');
    }
}
