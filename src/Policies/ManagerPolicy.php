<?php

namespace Esanj\Manager\Policies;

use Esanj\Manager\Enums\ManagerRoleEnum;
use Esanj\Manager\Models\Manager;

class ManagerPolicy
{
    public function before(Manager $actor): ?bool
    {
        return $actor->isAdmin() ? true : null;
    }

    /**
     * @param array<int, string> $permissions
     */
    public function create(Manager $actor, ?ManagerRoleEnum $role = null, array $permissions = []): bool
    {
        return $role !== ManagerRoleEnum::Admin && $this->holdsAll($actor, $permissions);
    }

    /**
     * @param array<int, string> $permissions
     */
    public function update(Manager $actor, Manager $target, ?ManagerRoleEnum $role = null, array $permissions = []): bool
    {
        return !$target->isAdmin()
            && $role !== ManagerRoleEnum::Admin
            && $this->holdsAll($actor, array_diff($permissions, $target->permissionKeys()));
    }

    public function delete(Manager $actor, Manager $target): bool
    {
        return !$target->isAdmin();
    }

    public function restore(Manager $actor, Manager $target): bool
    {
        return !$target->isAdmin();
    }

    /**
     * @param array<int, string> $permissions
     */
    private function holdsAll(Manager $actor, array $permissions): bool
    {
        return array_diff($permissions, $actor->permissionKeys()) === [];
    }
}
