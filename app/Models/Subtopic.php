<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subtopic extends Model
{
    protected $table = 'subtopics';

    public $timestamps = true;

    protected $fillable = [
        'topic_id',
        'name',
        'description',
        'order',
        'complexity_id',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'topic_id', 'id');
    }

    public function complexity(): BelongsTo
    {
        return $this->belongsTo(SubtopicComplexity::class, 'complexity_id', 'id');
    }

    public function prerequisites(): BelongsToMany
    {
        return $this->belongsToMany(
            Subtopic::class,
            'subtopic_prerequisites',
            'subtopic_id',
            'prerequisite_id'
        );
    }

    public function dependentSubtopics(): BelongsToMany
    {
        return $this->belongsToMany(
            Subtopic::class,
            'subtopic_prerequisites',
            'prerequisite_id',
            'subtopic_id'
        );
    }

    public function pathItems(): HasMany
    {
        return $this->hasMany(PathItem::class, 'subtopic_id', 'id');
    }

    public function planDays(): HasMany
    {
        return $this->hasMany(PlanDay::class, 'subtopic_id', 'id');
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class, 'subtopic_id', 'id');
    }

    public function subtopicLevelProgresses(): HasMany
    {
        return $this->hasMany(SubtopicLevelProgress::class, 'subtopic_id', 'id');
    }

    public function threads(): HasMany
    {
        return $this->hasMany(Thread::class, 'subtopic_id', 'id');
    }

    public function resources(): BelongsToMany
    {
        return $this->belongsToMany(
            EducationalResource::class,
            'resource_subtopics',
            'subtopic_id',
            'resource_id'
        );
    }
}
