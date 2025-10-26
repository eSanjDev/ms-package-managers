<?php

namespace Esanj\Manager\Facades;

use Esanj\Manager\Services\ManagerService;
use Illuminate\Support\Facades\Facade;

class Manager extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return ManagerService::class;
    }
}
