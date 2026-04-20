<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Difficulty extends Model
{
    protected $table = 'difficulties';

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
