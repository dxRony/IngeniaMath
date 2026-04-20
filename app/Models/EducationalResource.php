<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EducationalResource extends Model
{
    protected $table = 'educational_resources';

    public $timestamps = false;

    protected $fillable = [
        'type_id',
        'status_id',
        'title',
        'description',
        'url',
        'created_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ResourceType::class, 'type_id', 'id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ResourceStatus::class, 'status_id', 'id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function subtopics(): BelongsToMany
    {
        return $this->belongsToMany(
            Subtopic::class,
            'resource_subtopic',
            'resource_id',
            'subtopic_id'
        );
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ResourceReview::class, 'resource_id', 'id');
    }

    public function flashcards(): HasMany
    {
        return $this->hasMany(Flashcard::class, 'resource_id', 'id');
    }
}
