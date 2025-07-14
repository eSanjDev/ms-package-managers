<?php

namespace Esanj\Manager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagerPermission extends Model
{
    public $timestamps = false;

    public function Permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }
}
