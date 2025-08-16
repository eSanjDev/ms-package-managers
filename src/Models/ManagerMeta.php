<?php

namespace Esanj\Manager\Models;

use Esanj\Manager\Enums\ManagerRoleEnum;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class ManagerMeta extends Authenticatable
{
    use SoftDeletes;

    protected $fillable = [
        'manager_id',
        'key',
        'value',
    ];
}
