<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PracticeStatus extends Model
{
    protected $table = 'practice_statuses';

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function practiceSessions(): HasMany
    {
        return $this->hasMany(PracticeSession::class, 'status_id', 'id');
    }
}
