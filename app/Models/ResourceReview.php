<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceReview extends Model
{
    protected $table = 'resource_reviews';


    protected $fillable = [
        'resource_id',
        'reviewed_by',
        'is_approved',
        'feedback',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(EducationalResource::class, 'resource_id', 'id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'id');
    }
}
