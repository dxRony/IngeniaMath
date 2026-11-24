<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PathItem extends Model
{
    protected $table = 'path_items';


    protected $fillable = [
        'path_id',
        'subtopic_id',
        'mastery_level_id',
        'order',
        'is_completed',
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
        ];
    }

    public function path(): BelongsTo
    {
        return $this->belongsTo(LearningPath::class, 'path_id', 'id');
    }

    public function subtopic(): BelongsTo
    {
        return $this->belongsTo(Subtopic::class, 'subtopic_id', 'id');
    }

    public function masteryLevel(): BelongsTo
    {
        return $this->belongsTo(MasteryLevel::class, 'mastery_level_id', 'id');
    }
}
