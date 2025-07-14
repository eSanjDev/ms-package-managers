<?php

namespace Esanj\Manager\Models;

use Esanj\Manager\Enums\ManagerRoleEnum;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Manager extends Authenticatable
{
    use SoftDeletes;

    protected $fillable = [
        'esanj_id',
        'token',
        'role',
        'is_active',
        'last_login',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login' => 'datetime',
        'token' => 'hashed',
        'role' => ManagerRoleEnum::class,
    ];

    protected $hidden = [
        'token'
    ];

    public function Permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'manager_permissions');
    }
}
