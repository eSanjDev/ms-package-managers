<?php

namespace Esanj\Manager\Models;

use Esanj\Manager\Enums\ManagerRoleEnum;
use Esanj\Manager\Traits\HasManagerActivities;
use Esanj\Manager\Traits\HasManagerMeta;
use Esanj\Manager\Traits\HasManagerPermissions;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Manager extends Authenticatable
{
    use SoftDeletes, HasManagerPermissions, HasManagerActivities, HasManagerMeta;

    protected $fillable = [
        'esanj_id',
        'name',
        'token',
        'role',
        'is_active',
        'last_login',
        'extra',
        'secret_key',
        'uses_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'uses_token' => 'boolean',
        'last_login' => 'datetime',
        'token' => 'hashed',
        'role' => ManagerRoleEnum::class,
    ];

    protected $hidden = [
        'token',
        'secret_key',
    ];

    protected static function booted(): void
    {
        static::creating(function ($manager) {
            if (empty($manager->secret_key)) {
                $manager->secret_key = bin2hex(random_bytes(16));
            }
        });
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function needToken(): bool
    {
        return $this->uses_token;
    }
}
