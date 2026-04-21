<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedExercise extends Model
{
    protected $table = 'saved_exercises';


    protected $fillable = [
        'student_id',
        'session_exercise_id',
        'saved_at',
    ];

    protected function casts(): array
    {
        return [
            'saved_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id', 'id');
    }

    public function sessionExercise(): BelongsTo
    {
        return $this->belongsTo(SessionExercise::class, 'session_exercise_id', 'id');
    }
}
