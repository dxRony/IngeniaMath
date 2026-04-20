<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RelatedExercise extends Model
{
    protected $table = 'related_exercises';

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = [
        'exercise_id',
        'related_exercise_id',
    ];

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class, 'exercise_id', 'id');
    }

    public function relatedExercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class, 'related_exercise_id', 'id');
    }
}
