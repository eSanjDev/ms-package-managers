<?php

namespace Esanj\Manager\Traits;

use Esanj\Manager\Facades\Manager as ManagerFacade;
use Esanj\Manager\Models\Permission;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasManagerPermissions
{
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'manager_permissions');
    }

    public function hasPermission(string $permission): bool
    {
        return ManagerFacade::hasPermission($this->id, $permission);
    }
}
