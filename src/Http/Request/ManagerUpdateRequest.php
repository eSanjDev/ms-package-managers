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
            'is_active' => $this->boolean('is_active'),
            'uses_token' => $this->boolean('uses_token'),
        ]);
    }
}
