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
        'name',
        'token',
        'role',
        'is_active',
        'last_login',
        'extra',
        'secret_key',
        'uses_token'
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

    public function Permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'manager_permissions');
    }

    public function getMeta($key)
    {
        return $this->meta->where('key', $key)->value('value');
    }

    public function meta()
    {
        return $this->hasMany(ManagerMeta::class);
    }

    public function setMeta(string $key, $value)
    {
        return $this->meta()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
