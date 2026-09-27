<?php

namespace Esanj\Manager\Http\Request;

use Esanj\Manager\Enums\ManagerRoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class ManagerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', Rule::in(ManagerRoleEnum::toArray())],
            'token' => ['nullable', 'string', 'max:' . config('esanj.manager.token_length', 128)],
            'is_active' => ['boolean'],
            'uses_token' => ['boolean'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,key'],
        ];
    }

    public function prepareForValidation(): void
    {
        // The forms send an empty "permissions" when nothing is ticked.
        if ($this->exists('permissions') && in_array($this->input('permissions'), [null, ''], true)) {
            $this->merge(['permissions' => []]);
        }

        foreach (['is_active', 'uses_token'] as $field) {
            if ($this->has($field)) {
                $this->merge([$field => $this->boolean($field)]);
            }
        }
    }

    protected function requestedRole(): ?ManagerRoleEnum
    {
        $role = $this->input('role');

        return is_string($role) ? ManagerRoleEnum::tryFrom($role) : null;
    }

    /**
     * @return array<int, string>
     */
    protected function requestedPermissions(): array
    {
        return array_values(array_filter((array) $this->input('permissions', []), 'is_string'));
    }
}
