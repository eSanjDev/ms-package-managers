<?php

namespace Esanj\Manager\Exceptions;

class ManagerAccessDenied extends ManagerException
{
    public static function managerNotFound(): self
    {
        return new self(
            message: __('manager::manager.errors.access_denied'),
            code: 403
        );
    }

    public static function managerNotActive(): self
    {
        return new self(
            message: __('manager::manager.errors.manager_not_active'),
            code: 403
        );
    }

    public static function wrongToken(): self
    {
        return new self(
            message: __('manager::manager.errors.token_incorrect'),
            code: 403
        );
    }
}
