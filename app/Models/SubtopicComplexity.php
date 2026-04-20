<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubtopicComplexity extends Model
{
    protected $table = 'subtopic_complexities';

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function subtopics(): HasMany
    {
        return $this->hasMany(Subtopic::class, 'complexity_id', 'id');
    }
}
