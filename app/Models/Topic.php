<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Topic extends Model
{
    protected $table = 'topics';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'description',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function subtopics(): HasMany
    {
        return $this->hasMany(Subtopic::class, 'topic_id', 'id');
    }

    public function assessmentModuleResults(): HasMany
    {
        return $this->hasMany(AssessmentModuleResult::class, 'topic_id', 'id');
    }

    public function moduleProgress(): HasMany
    {
        return $this->hasMany(ModuleProgress::class, 'topic_id', 'id');
    }

    public function practiceSessions(): HasMany
    {
        return $this->hasMany(PracticeSession::class, 'topic_id', 'id');
    }

    public function sessionTopicSummaries(): HasMany
    {
        return $this->hasMany(SessionTopicSummary::class, 'topic_id', 'id');
    }

    public function examTopicDistributions(): HasMany
    {
        return $this->hasMany(ExamTopicDistribution::class, 'topic_id', 'id');
    }

    public function getForumBorderClassAttribute(): string
    {
        return match ((int) $this->id) {
            1 => 'border-primary',
            2 => 'border-success',
            3 => 'border-warning',
            4 => 'border-info',
            5 => 'border-danger',
            6 => 'border-secondary',
            7 => 'border-dark',
            default => 'border-light',
        };
    }
}
