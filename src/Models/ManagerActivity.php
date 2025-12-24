<?php

namespace Esanj\Manager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagerActivity extends Model
{
    protected $fillable = [
        'manager_id',
        'type',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Manager::class);
    }
}
