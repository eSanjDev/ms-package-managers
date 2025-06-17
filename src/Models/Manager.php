<?php

namespace Esanj\Manager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Manager extends Model
{
    use SoftDeletes;

    protected $table = 'oauth_managers';

    protected $fillable = [
        'manager_id',
        'token',
        'is_active',
        'last_login',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login' => 'datetime',
        'token' => 'hashed'
    ];
}
