<?php

namespace Esanj\Manager\Http\Request;

use Illuminate\Foundation\Http\FormRequest;

class ManagerAuthRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string'],
            'token' => ['nullable', 'string'],
        ];
    }
}
