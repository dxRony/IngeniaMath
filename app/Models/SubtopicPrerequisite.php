<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubtopicPrerequisite extends Model
{
    protected $table = 'subtopic_prerequisites';

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = [
        'subtopic_id',
        'prerequisite_id',
    ];

    public function subtopic(): BelongsTo
    {
        return $this->belongsTo(Subtopic::class, 'subtopic_id', 'id');
    }

    public function prerequisite(): BelongsTo
    {
        return $this->belongsTo(Subtopic::class, 'prerequisite_id', 'id');
    }
}
