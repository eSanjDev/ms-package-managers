<?php

namespace Esanj\Manager\Services;

use Esanj\Manager\Enums\ManagerRoleEnum;
use Esanj\Manager\Models\Manager;
use Esanj\Manager\Repositories\ManagerRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class ManagerService
{
    public function __construct(
        protected ManagerRepository $repository
    )
    {
    }

    public function getManagersWithPaginate(): LengthAwarePaginator
    {
        $request = \request();

        $perPage = min((int)$request->get('per_page', 15), 50);

        $query = Manager::query();

        if ($request->boolean('only_trash')) {
            $query->onlyTrashed();
        }

        if ($request->filled('search')) {
            $query->where(function ($query) use ($request) {
                return $query->where('name', 'like', '%' . $request->get('search') . '%');
            });
        }

        return $query->paginate($perPage);
    }

    public function findByEsanjId(int $esanjId): ?Manager
    {
        return $this->repository->findByEsanjId($esanjId);
    }

    public function findById(int $id): ?Manager
    {
        return $this->repository->findById($id);
    }

    public function createManager(array $data): Manager
    {
        return $this->repository->create($data);
    }

    public function updateManager(int $id, array $data): ?Manager
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function restoreManager(int $id): ?Manager
    {
        return $this->repository->restore($id);
    }

    public function checkManagerToken(?Manager $manager, string $token): bool
    {
        return $manager && Hash::check($token, $manager->token);
    }

    public function updateLastLogin(int $id): ?Manager
    {
        return $this->repository->update($id, [
            'last_login' => Carbon::now(),
        ]);
    }

    public function hasPermission(int $id, string $permission): bool
    {
        $manager = $this->findById($id);

        if (!$manager) {
            return false;
        }

        // Admin has all permissions
        return $manager->role === ManagerRoleEnum::Admin ||
            $manager->permissions->contains('key', $permission);
    }

    public function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    public function getActivitiesWithPaginate(Manager $manager): LengthAwarePaginator
    {
        $request = \request();

        $perPage = min((int)$request->get('per_page', 15), 50);

        $query = $manager->activities();

        if ($request->filled('search')) {
            $query = $query->where("type", $request->get('search'))->orWhere("meta", $request->get('search'));
        }

        return $query->paginate($perPage);
    }

    public function setActivity(string $type, array $meta = [])
    {
        return auth()->user()->setActivity($type, $meta);
    }

    public function getActivities(int|Manager $manager)
    {
        if (is_int($manager)) {
            $manager = $this->findById($manager);
        }

        return $manager->activities;
    }
}
