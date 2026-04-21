<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DuplicateAlert extends Model
{
    protected $table = 'duplicate_alerts';

    protected $fillable = [
        'new_exercise_id',
        'existing_exercise_id',
        'similarity',
        'is_resolved',
    ];

    protected function casts(): array
    {
        return [
            'similarity' => 'float',
            'is_resolved' => 'boolean',
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
