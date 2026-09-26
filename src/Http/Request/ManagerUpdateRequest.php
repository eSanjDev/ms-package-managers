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
            'token' => ['nullable', 'string', 'max:' . config('esanj.manager.token_length', 128)],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'uses_token' => ['boolean'],
            'permissions' => ['array', Rule::requiredIf($isNotAdmin)],
            'permissions.*' => ['exists:permissions,key'],
        ];
    }

    public function prepareForValidation(): void
    {
        foreach (['is_active', 'uses_token'] as $field) {
            if ($this->has($field)) {
                $this->merge([$field => $this->boolean($field)]);
            }
        }
    }
}
