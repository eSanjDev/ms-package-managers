<?php

namespace Esanj\Manager\Enums;

enum AuthManagerStatusResponsesEnum: string
{
    case SUCCESSFUL = 'Successful';
    case TOKEN_INCORRECT = 'token_incorrect';
    case PUBLIC_KEY_NOT_FOUND = 'public_key_not_found';
    case INVALID_TOKEN = 'invalid_token';
    case TOKEN_NOT_FOUND = 'token_not_found';
    case TOO_MANY_ATTEMPTS = 'too_many_attempts';

    public function message(array $params = []): string
    {
        return trans("manager::manager.errors.{$this->value}", $params);
    }
}
