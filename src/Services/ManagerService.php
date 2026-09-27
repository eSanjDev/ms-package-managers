<?php

namespace Esanj\Manager\Services;

use Esanj\Manager\Exceptions\ManagerAccessDenied;
use Esanj\Manager\Models\Manager;
use Esanj\Manager\Models\Permission;
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

        $perPage = $this->perPage();

        $query = Manager::query();

        if ($request->boolean('only_trash')) {
            $query->onlyTrashed();
        }

        if (($search = $this->searchTerm()) !== null) {
            $query->where('name', 'like', '%' . $search . '%');
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
        $permissionKeys = $this->pullPermissionKeys($data);

        $manager = $this->repository->create($data);

        if (is_array($permissionKeys)) {
            $this->syncPermissions($manager, $permissionKeys);
        }

        $this->logActivity('manager.created', [
            'target_id' => $manager->id,
            'target_name' => $manager->name,
        ]);

        return $manager;
    }

    public function updateManager(int $id, array $data): ?Manager
    {
        $permissionKeys = $this->pullPermissionKeys($data);

        $manager = $this->repository->update($id, $data);

        if ($manager) {
            if (is_array($permissionKeys)) {
                $this->syncPermissions($manager, $permissionKeys);
            }

            $this->logActivity('manager.updated', [
                'target_id' => $manager->id,
                'target_name' => $manager->name,
                'changes' => array_keys($data),
            ]);
        }

        return $manager;
    }

    /**
     * @return array<int, string>|null
     */
    protected function pullPermissionKeys(array &$data): ?array
    {
        if (!array_key_exists('permissions', $data)) {
            return null;
        }

        $permissions = $data['permissions'];
        unset($data['permissions']);

        return is_array($permissions) ? $permissions : [];
    }

    /**
     * @param array<int, string> $permissionKeys
     */
    protected function syncPermissions(Manager $manager, array $permissionKeys): void
    {
        $actor = $this->actor();

        // A non-admin only controls the permissions they hold; the target keeps the rest.
        if ($actor && !$actor->isAdmin()) {
            $permissionKeys = array_unique([
                ...$permissionKeys,
                ...array_diff($manager->permissionKeys(), $actor->permissionKeys()),
            ]);
        }

        $permissionIds = Permission::whereIn('key', $permissionKeys)->pluck('id');

        $manager->permissions()->sync($permissionIds);
        $manager->load('permissions');
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

    /**
     * @throws ManagerAccessDenied
     */
    public function checkManagerToken(Manager $manager, string $token): bool
    {
        if (Hash::check($token, $manager->token)) {
            return true;
        }

        throw ManagerAccessDenied::wrongToken();
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

        return $manager->isAdmin() || $manager->permissions->contains('key', $permission);
    }

    public function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    public function getActivitiesWithPaginate(Manager $manager): LengthAwarePaginator
    {
        $perPage = $this->perPage();

        $query = $manager->activities();

        if (($search = $this->searchTerm()) !== null) {
            $query->where(function ($q) use ($search) {
                $q->where('type', 'like', '%' . $search . '%')
                    ->orWhereJsonContains('meta', $search);
            });
        }

        return $query->paginate($perPage);
    }

    private function searchTerm(): ?string
    {
        $search = request()->input('search');

        return is_string($search) && trim($search) !== '' ? trim($search) : null;
    }

    private function perPage(): int
    {
        $perPage = (int) request()->get('per_page', 15);

        return $perPage < 1 ? 15 : min($perPage, 50);
    }

    private function actor(): ?Manager
    {
        $manager = auth('manager')->user();

        return $manager instanceof Manager ? $manager : null;
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
        $currentUser = $this->actor();

        if (!$currentUser) {
            return;
        }

        $meta['performed_by'] = [
            'id' => $currentUser->id,
            'name' => $currentUser->name,
        ];

        $this->logActivityFor($currentUser, $type, $meta);
    }
}
