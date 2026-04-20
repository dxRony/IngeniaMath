<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentModuleResult extends Model
{
    protected $table = 'assessment_module_results';

    public $timestamps = false;

    protected $fillable = [
        'assessment_id',
        'module_id',
        'mastery_level_id',
        'score',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(InitialAssessment::class, 'assessment_id', 'id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'module_id', 'id');
    }

    public function masteryLevel(): BelongsTo
    {
        return $this->belongsTo(MasteryLevel::class, 'mastery_level_id', 'id');
    }
}
