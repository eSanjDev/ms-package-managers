<?php

namespace Esanj\Manager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Manager extends Model
{
    use SoftDeletes;

    protected $table = 'oauth_managers';

    protected $fillable = [
        'sub_id',
        'token',
        'is_active',
        'last_login',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_login' => 'datetime',
            'token' => 'hashed'
        ];
    }
}
