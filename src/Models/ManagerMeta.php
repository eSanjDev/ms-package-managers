<?php

namespace Esanj\Manager\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class ManagerMeta extends Authenticatable
{
    protected $fillable = [
        'manager_id',
        'key',
        'value',
    ];
}
