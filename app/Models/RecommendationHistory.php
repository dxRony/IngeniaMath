<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecommendationHistory extends Model
{
    protected $table = 'recommendation_history';

    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'exercise_id',
        'student_id',
        'reason_id',
        'recommended_at',
    ];

    protected function casts(): array
    {
        return [
            'recommended_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(PracticeSession::class, 'session_id', 'id');
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class, 'exercise_id', 'id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id', 'id');
    }

    public function reason(): BelongsTo
    {
        return $this->belongsTo(RecommendationReason::class, 'reason_id', 'id');
    }
}
