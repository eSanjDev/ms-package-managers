<?php

namespace Esanj\Manager\Enums;

enum AuthManagerStatusResponsesEnum: string
{
    case successful = 'Successful';
    case token_incorrect = 'The token you entered is incorrect';
    case public_key_not_found = 'Public key not found';
    case invalid_token = 'Invalid token';
    case not_found_token = 'Token not found';
    case too_many_attempts = 'Too many attempts. Please try again later.';
}
