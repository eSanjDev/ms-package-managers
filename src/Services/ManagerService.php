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
        $manager = $this->repository->create($data);

        $this->logActivity('manager.created', [
            'target_id' => $manager->id,
            'target_name' => $manager->name,
        ]);

        return $manager;
    }

    public function updateManager(int $id, array $data): ?Manager
    {
        $manager = $this->repository->update($id, $data);

        if ($manager) {
            $this->logActivity('manager.updated', [
                'target_id' => $manager->id,
                'target_name' => $manager->name,
                'changes' => array_keys($data),
            ]);
        }

        return $manager;
    }

    public function delete(int $id): bool
    {
        $manager = $this->findById($id);
        $result = $this->repository->delete($id);

        if ($result && $manager) {
            $this->logActivity('manager.deleted', [
                'target_id' => $manager->id,
                'target_name' => $manager->name,
            ]);
        }

        return $result;
    }

    public function restoreManager(int $id): ?Manager
    {
        $manager = $this->repository->restore($id);

        if ($manager) {
            $this->logActivity('manager.restored', [
                'target_id' => $manager->id,
                'target_name' => $manager->name,
            ]);
        }

        return $manager;
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
        $request = request();
        $perPage = min((int) $request->get('per_page', 15), 50);

        $query = $manager->activities();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('type', 'like', '%' . $search . '%')
                  ->orWhereJsonContains('meta', $search);
            });
        }

        return $query->paginate($perPage);
    }

    public function setActivity(string $type, array $meta = [])
    {
        return $this->logActivity($type, $meta);
    }

    public function getActivities(int|Manager $manager)
    {
        if (is_int($manager)) {
            $manager = $this->findById($manager);
        }

        return $manager->activities;
    }

    public function logActivityFor(Manager $manager, string $type, array $meta = []): void
    {
        $meta['ip_address'] = request()->ip();
        $meta['user_agent'] = request()->userAgent();

        $manager->setActivity($type, $meta);
    }

    protected function logActivity(string $type, array $meta = []): void
    {
        $currentUser = auth('manager')->user();

        if (!$currentUser instanceof Manager) {
            return;
        }

        $meta['performed_by'] = [
            'id' => $currentUser->id,
            'name' => $currentUser->name,
        ];

        $this->logActivityFor($currentUser, $type, $meta);
    }
}
