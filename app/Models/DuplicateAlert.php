<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DuplicateAlert extends Model
{
    protected $table = 'duplicate_alerts';

    public $timestamps = false;

    protected $fillable = [
        'new_exercise_id',
        'existing_exercise_id',
        'similarity',
        'created_at',
        'is_reviewed',
    ];

    protected function casts(): array
    {
        return [
            'similarity' => 'float',
            'created_at' => 'datetime',
            'is_reviewed' => 'boolean',
        ];
    }

    public function newExercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class, 'new_exercise_id', 'id');
    }

    public function existingExercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class, 'existing_exercise_id', 'id');
    }
}
