<?php

namespace Esanj\Manager\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    public $timestamps = false;

    protected $fillable = ['key', 'display_name', 'description'];
}
