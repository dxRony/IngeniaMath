<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecommendationReason extends Model
{
    protected $table = 'recommendation_reasons';

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function recommendationHistories(): HasMany
    {
        return $this->hasMany(RecommendationHistory::class, 'reason_id', 'id');
    }
}
