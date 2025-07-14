<?php

namespace Esanj\Manager\Enums;

enum ManagerRoleEnum : string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Operator = 'operator';

    public  static function toArray() : array
    {
        return array_column(self::cases(), 'value');
    }
}
