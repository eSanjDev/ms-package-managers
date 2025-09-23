<?php

namespace Esanj\Manager\Http\Request;

use Esanj\Manager\Enums\ManagerRoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ManagerUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $isNotAdmin = $this->input('role') !== ManagerRoleEnum::Admin->value;

        return [
            'role' => ['required', Rule::in(ManagerRoleEnum::toArray())],
            'token' => ['nullable', 'string', 'max:' . config('esanj.manager.token_length')],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'uses_token' => ['boolean'],
            'permissions' => ['array', Rule::requiredIf($isNotAdmin)],
            'permissions.*' => ['exists:permissions,id'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->input('is_active') == '1',
            'uses_token' => $this->input('uses_token') == '1',
        ]);
    }
}
