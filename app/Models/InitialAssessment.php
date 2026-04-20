<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InitialAssessment extends Model
{
    protected $table = 'initial_assessments';

    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'created_at',
        'is_completed',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'is_completed' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id', 'id');
    }

    public function assessmentExercises(): HasMany
    {
        return $this->hasMany(AssessmentExercise::class, 'assessment_id', 'id');
    }

    public function moduleResults(): HasMany
    {
        return $this->hasMany(AssessmentModuleResult::class, 'assessment_id', 'id');
    }
}
