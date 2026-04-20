<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExerciseStatus extends Model
{
    protected $table = 'exercise_statuses';

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class, 'status_id', 'id');
    }
}
