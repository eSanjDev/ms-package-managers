<?php

namespace Esanj\Manager\Exceptions;

class SessionExpiredException extends ManagerException
{
    public static function make(): self
    {
        return new self('Session expired.', 401);
    }
}
