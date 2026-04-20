<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Flashcard extends Model
{
    protected $table = 'flashcards';

    public $timestamps = false;

    protected $fillable = [
        'resource_id',
        'front_text',
        'back_text',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(EducationalResource::class, 'resource_id', 'id');
    }
}
