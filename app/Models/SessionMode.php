<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SessionMode extends Model
{
    protected $table = 'session_modes';

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function practiceSessions(): HasMany
    {
        return $this->hasMany(PracticeSession::class, 'mode_id', 'id');
    }
}
