<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleProgress extends Model
{
    protected $table = 'module_progress';

    public $timestamps = true;

    protected $fillable = [
        'score',
        'student_id',
        'topic_id',
        'mastery_level_id',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'id');
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id', 'id');
    }

    public function masteryLevel()
    {
        return $this->belongsTo(MasteryLevel::class, 'mastery_level_id', 'id');
    }
}
