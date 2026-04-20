<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExerciseLevel extends Model
{
    protected $table = 'exercise_levels';

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class, 'level_id', 'id');
    }
}
