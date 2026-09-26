<?php

namespace Esanj\Manager\Http\Request;

class ManagerUpdateRequest extends ManagerRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user('manager')?->can('update', [
            $this->route('manager'),
            $this->requestedRole(),
            $this->requestedPermissions(),
        ]);
    }
}
