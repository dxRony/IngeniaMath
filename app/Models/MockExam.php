<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MockExam extends Model
{
    protected $table = 'mock_exams';

    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'config_id',
        'started_at',
        'ended_at',
        'score',
        'is_completed',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'score' => 'float',
            'is_completed' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id', 'id');
    }

    public function config(): BelongsTo
    {
        return $this->belongsTo(ExamConfig::class, 'config_id', 'id');
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(MockExamExercise::class, 'exam_id', 'id');
    }
}
