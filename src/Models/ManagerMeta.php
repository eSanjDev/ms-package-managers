<?php

namespace Esanj\Manager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagerMeta extends Model
{
    protected $fillable = [
        'manager_id',
        'key',
        'value',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Manager::class);
    }
}
