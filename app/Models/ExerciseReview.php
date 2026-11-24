<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseReview extends Model
{
    protected $table = 'exercise_reviews';

    public $timestamps = true;

    protected $fillable = [
        'exercise_id',
        'reviewed_by',
        'is_approved',
        'feedback',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class, 'exercise_id', 'id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'id');
    }
}
