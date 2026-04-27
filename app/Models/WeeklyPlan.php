<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeeklyPlan extends Model
{
    protected $table = 'weekly_plans';


    protected $fillable = [
        'path_id',
        'week_start',
        'available_hours',
    ];

    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'available_hours' => 'float',
        ];
    }

    public function path(): BelongsTo
    {
        return $this->belongsTo(LearningPath::class, 'path_id', 'id');
    }

    public function planDays(): HasMany
    {
        return $this->hasMany(PlanDay::class, 'weekly_plan_id', 'id')->orderBy('weekday');
    }
}
