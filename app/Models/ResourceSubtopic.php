<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceSubtopic extends Model
{
    protected $table = 'resource_subtopics';

    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = [
        'resource_id',
        'subtopic_id',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(EducationalResource::class, 'resource_id', 'id');
    }

    public function subtopic(): BelongsTo
    {
        return $this->belongsTo(Subtopic::class, 'subtopic_id', 'id');
    }
}
