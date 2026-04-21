<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasteryLevel extends Model
{
    protected $table = 'mastery_levels';


    protected $fillable = [
        'name',
        'min_threshold',
        'max_threshold',
    ];

    public function assessmentModuleResults(): HasMany
    {
        return $this->hasMany(AssessmentModuleResult::class, 'mastery_level_id', 'id');
    }

    public function pathItems(): HasMany
    {
        return $this->hasMany(PathItem::class, 'mastery_level_id', 'id');
    }
}
