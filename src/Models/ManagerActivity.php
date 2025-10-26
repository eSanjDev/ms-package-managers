<?php

namespace Esanj\Manager\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;

class ManagerActivity extends Authenticatable
{
    protected $fillable = [
        'manager_id',
        'type',
        'meta',
    ];

    protected $casts = [
        'meta' => 'json',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Manager::class);
    }

}
