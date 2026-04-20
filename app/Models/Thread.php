<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Thread extends Model
{
    protected $table = 'threads';

    public $timestamps = false;

    protected $fillable = [
        'subtopic_id',
        'title',
        'description',
        'created_by',
        'created_at',
        'is_closed',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'is_closed' => 'boolean',
        ];
    }

    public function subtopic(): BelongsTo
    {
        return $this->belongsTo(Subtopic::class, 'subtopic_id', 'id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'thread_id', 'id');
    }
}
