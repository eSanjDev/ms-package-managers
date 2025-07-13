<?php

namespace Esanj\Manager\Services;

use Esanj\Manager\Enums\ManagerRoleEnum;
use Esanj\Manager\Models\Manager;
use Esanj\Manager\Repositories\ManagerRepository;
use Illuminate\Support\Facades\Hash;

class ManagerService
{
    public function __construct(protected ManagerRepository $repository)
    {
    }

    public function findByManagerID(int $id)
    {
        return $this->repository->findByMangerId($id);
    }

    public function checkManagerToken(Manager $manager = null, string $token): bool
    {
        if ($manager && Hash::check($token, $manager->token)) {
            return true;
        }

        return false;
    }

    public function updateLastLogin(int $id)
    {
        return $this->repository->update($id, ['last_login' => now()]);
    }

    public function updateManager(int $id, array $data): Manager
    {
        return $this->repository->update($id, $data);
    }

    public function createManager(int $manager_id, string $token, ManagerRoleEnum $role = ManagerRoleEnum::Manager): Manager
    {
        return $this->repository->create([
            'manager_id' => $manager_id,
            'token' => $token,
            'role' => $role
        ]);
    }

    public function switchToInactive(int $managerID)
    {
        $id = $this->repository->findByMangerId($managerID)?->id;
        return $this->repository->update($id, ['is_active' => 0]);
    }

    public function switchToActive(int $managerID)
    {
        $id = $this->repository->findByMangerId($managerID)?->id;
        return $this->repository->update($id, ['is_active' => 1]);
    }
}
