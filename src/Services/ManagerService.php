<?php

namespace Esanj\Manager\Services;

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

    public function checkManagerToken(Manager $manager, string $token): bool
    {
        if ($manager && $manager->is_active && Hash::check($token, $manager->token)) {
            return true;
        }

        return false;
    }

    public function updateLastLogin(int $id)
    {
        return $this->repository->update($id, ['last_login' => now()]);
    }

    public function createManager(int $id, string $token): Manager
    {
        return $this->repository->create([
            'manager_id' => $id,
            'token' => $token,
        ]);
    }

    public function SwitchToInactive(int $managerID)
    {
        $id = $this->repository->findByMangerId($managerID)?->id;
        return $this->repository->update($id, ['is_active' => 0]);
    }

    public function SwitchToActive(int $managerID)
    {
        $id = $this->repository->findByMangerId($managerID)?->id;
        return $this->repository->update($id, ['is_active' => 1]);
    }
}
