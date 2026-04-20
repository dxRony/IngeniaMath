<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    protected $table = 'exercises';

    public $timestamps = true;

    protected $fillable = [
        'subtopic_id',
        'level_id',
        'type_id',
        'status_id',
        'statement',
        'image_url',
        'correct_answer',
        'estimated_minutes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'estimated_minutes' => 'integer',
        ];
    }

    public function subtopic(): BelongsTo
    {
        return $this->belongsTo(Subtopic::class, 'subtopic_id', 'id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(ExerciseLevel::class, 'level_id', 'id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ExerciseType::class, 'type_id', 'id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ExerciseStatus::class, 'status_id', 'id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ExerciseStep::class, 'exercise_id', 'id');
    }

    public function multipleChoiceOptions(): HasMany
    {
        return $this->hasMany(MultipleChoiceOption::class, 'exercise_id', 'id');
    }

    public function assessmentExercises(): HasMany
    {
        return $this->hasMany(AssessmentExercise::class, 'exercise_id', 'id');
    }

    public function sessionExercises(): HasMany
    {
        return $this->hasMany(SessionExercise::class, 'exercise_id', 'id');
    }

    public function recommendationHistories(): HasMany
    {
        return $this->hasMany(RecommendationHistory::class, 'exercise_id', 'id');
    }

    public function mockExams(): BelongsToMany
    {
        return $this->belongsToMany(
            MockExam::class,
            'mock_exam_exercises',
            'exercise_id',
            'exam_id'
        )->withPivot([
            'order',
            'given_answer',
            'resolution_seconds',
            'answered_at',
            'is_correct',
        ]);
    }

    public function relatedExercises(): BelongsToMany
    {
        return $this->belongsToMany(
            Exercise::class,
            'related_exercises',
            'exercise_id',
            'related_exercise_id'
        );
    }

    public function relatedToExercises(): BelongsToMany
    {
        return $this->belongsToMany(
            Exercise::class,
            'related_exercises',
            'related_exercise_id',
            'exercise_id'
        );
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ExerciseReview::class, 'exercise_id', 'id');
    }

    public function duplicateAlertsAsNew(): HasMany
    {
        return $this->hasMany(DuplicateAlert::class, 'new_exercise_id', 'id');
    }

    public function duplicateAlertsAsExisting(): HasMany
    {
        return $this->hasMany(DuplicateAlert::class, 'existing_exercise_id', 'id');
    }
}
