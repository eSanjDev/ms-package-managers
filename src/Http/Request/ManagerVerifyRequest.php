<?php

namespace Esanj\Manager\Http\Request;

use Esanj\Manager\Enums\ManagerRoleEnum;
use Illuminate\Foundation\Http\FormRequest;

class ManagerVerifyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string']
        ];
    }
}
