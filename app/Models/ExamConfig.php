<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamConfig extends Model
{
    protected $table = 'exam_configs';

    public $timestamps = false;

    protected $fillable = [
        'duration_minutes',
        'total_questions',
        'created_at',
        'created_by',
        'is_active',
        'min_passing_score',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'total_questions' => 'integer',
            'created_at' => 'datetime',
            'is_active' => 'boolean',
            'min_passing_score' => 'float',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function topicDistributions(): HasMany
    {
        return $this->hasMany(ExamTopicDistribution::class, 'config_id', 'id');
    }

    public function mockExams(): HasMany
    {
        return $this->hasMany(MockExam::class, 'config_id', 'id');
    }
}
