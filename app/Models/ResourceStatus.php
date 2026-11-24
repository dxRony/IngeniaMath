<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResourceStatus extends Model
{
    protected $table = 'resource_statuses';

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function resources(): HasMany
    {
        return $this->hasMany(EducationalResource::class, 'status_id', 'id');
    }
}
