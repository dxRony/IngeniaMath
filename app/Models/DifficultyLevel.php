<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DifficultyLevel extends Model
{
    protected $table = 'difficulty_levels';

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function practiceSessions(): HasMany
    {
        return $this->hasMany(PracticeSession::class, 'initial_difficulty_id', 'id');
    }

    public function subtopicLevelProgresses(): HasMany
    {
        return $this->hasMany(SubtopicLevelProgress::class, 'current_difficulty_id', 'id');
    }
}
