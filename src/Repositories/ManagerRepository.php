<?php

namespace Esanj\Manager\Repositories;

use Esanj\Manager\Models\Manager;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;

class ManagerRepository
{
    // Holds the manager id only; the older '_esanj_id' entries held the whole model.
    private const KEY_SUFFIX_ESANJ_ID = '_esanj';
    private const KEY_SUFFIX_MANAGER_ID = '_manager_id';
    private const KEY_SUFFIX_VERSION = '_version';

    public function __construct(
        protected Manager         $model,
        protected CacheRepository $cache
    )
    {
    }

    public function findById(int $id): ?Manager
    {
        $find = fn () => $this->model->with('permissions')->find($id);

        if (!$this->cacheEnabled()) {
            return $find();
        }

        return $this->remember($this->makeCacheKey($id, self::KEY_SUFFIX_MANAGER_ID . $this->version($id)), $find);
    }

    public function findByEsanjId(int $esanjId): ?Manager
    {
        $find = fn () => $this->model->where('esanj_id', $esanjId)->value('id');

        $id = $this->cacheEnabled()
            ? $this->remember($this->makeCacheKey($esanjId, self::KEY_SUFFIX_ESANJ_ID), $find)
            : $find();

        return $id ? $this->findById((int) $id) : null;
    }

    public function create(array $data): Manager
    {
        $manager = $this->model->create($data);
        $this->clearCache($manager);
        return $manager;
    }

    public function update(int $id, array $data): ?Manager
    {
        $manager = $this->findById($id);

        if ($manager) {
            $manager->update($data);
            $this->clearCache($manager);
        }

        return $manager;
    }

    /**
     * @param iterable<int> $permissionIds
     */
    public function syncPermissions(Manager $manager, iterable $permissionIds): void
    {
        $manager->permissions()->sync($permissionIds);
        $manager->load('permissions');
        $this->clearCache($manager);
    }

    public function delete(int $id): bool
    {
        $manager = $this->findById($id);

        if ($manager) {
            $manager->delete();
            $this->clearCache($manager);
            return true;
        }

        return false;
    }

    public function restore(int $id): ?Manager
    {
        $manager = $this->model->withTrashed()->findOrFail($id);
        $manager->restore();
        $this->clearCache($manager);

        return $this->findById($id);
    }

    private function remember(string $key, callable $callback): mixed
    {
        return $this->getCacheRepository()->remember($key, $this->getCacheTtlInSeconds(), $callback);
    }

    protected function clearCache(Manager $manager): void
    {
        $cache = $this->getCacheRepository();

        $cache->forever($this->makeCacheKey($manager->id, self::KEY_SUFFIX_VERSION), bin2hex(random_bytes(8)));
        $cache->forget($this->makeCacheKey($manager->esanj_id, self::KEY_SUFFIX_ESANJ_ID));
    }

    private function version(int $id): string
    {
        return (string) ($this->getCacheRepository()->get($this->makeCacheKey($id, self::KEY_SUFFIX_VERSION)) ?? '');
    }

    private function makeCacheKey(int|string $id, string $suffix): string
    {
        return $this->getCachePrefix() . $id . $suffix;
    }

    private function cacheEnabled(): bool
    {
        return (bool) config('esanj.manager.cache.is_enabled', true);
    }

    private function getCacheTtlInSeconds(): int
    {
        return (int) config('esanj.manager.cache.ttl', 60) * 60;
    }

    private function getCachePrefix(): string
    {
        return config('esanj.manager.cache.prefix', 'manager_');
    }

    private function getCacheRepository(): CacheRepository
    {
        $driver = config('esanj.manager.cache.driver', config('cache.default'));
        return Cache::driver($driver);
    }
}
