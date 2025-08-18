<?php

namespace Esanj\Manager\Http\Request;

use Esanj\Manager\Enums\ManagerRoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ManagerCreateRequest extends FormRequest
{
    public function rules(): array
    {
        $isNotAdmin = $this->input('role') !== ManagerRoleEnum::Admin->value;

        return [
            'esanj_id' => ['required', 'integer', 'unique:managers,esanj_id'],
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', Rule::in(ManagerRoleEnum::toArray())],
            'token' => ['nullable', 'string', 'max:' . config('esanj.manager.token_length')],
            'is_active' => ['boolean'],
            'permissions' => ['array', Rule::requiredIf($isNotAdmin)],
            'permissions.*' => ['exists:permissions,id'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->input('is_active') == 'on',
        ]);
    }
}
