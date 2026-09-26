<?php

namespace Esanj\Manager\Http\Request;

use Esanj\Manager\Models\Manager;

class ManagerCreateRequest extends ManagerRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user('manager')?->can('create', [
            Manager::class,
            $this->requestedRole(),
            $this->requestedPermissions(),
        ]);
    }

    public function rules(): array
    {
        return [
            'esanj_id' => ['required', 'integer', 'unique:managers,esanj_id'],
            ...parent::rules(),
        ];
    }
}
