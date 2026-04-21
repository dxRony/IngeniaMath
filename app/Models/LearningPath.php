<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningPath extends Model
{
    protected $table = 'learning_paths';


    protected $fillable = [
        'student_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PathItem::class, 'path_id', 'id');
    }

    public function weeklyPlans(): HasMany
    {
        return $this->hasMany(WeeklyPlan::class, 'path_id', 'id');
    }
}
