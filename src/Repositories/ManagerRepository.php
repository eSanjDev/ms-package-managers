<?php

namespace Esanj\Manager\Repositories;

use Esanj\Manager\Models\Manager;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;

class ManagerRepository
{
    private const KEY_SUFFIX_ESANJ_ID = '_esanj_id';
    private const KEY_SUFFIX_MANAGER_ID = '_manager_id';

    public function __construct(
        protected Manager         $model,
        protected CacheRepository $cache
    )
    {
    }

    public function findById(int $id): ?Manager
    {
        return $this->rememberInCache($id, self::KEY_SUFFIX_MANAGER_ID, function () use ($id) {
            return $this->model->with('permissions')->find($id);
        });
    }

    public function findByEsanjId(int $esanjId): ?Manager
    {
        $manager = $this->rememberInCache($esanjId, self::KEY_SUFFIX_ESANJ_ID, function () use ($esanjId) {
            return $this->model->where('esanj_id', $esanjId)->first();
        });

        return $manager ? $this->findById($manager->id) : null;
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
        $this->model->withTrashed()->findOrFail($id)->restore();
        $manager = $this->findById($id);
        if ($manager) {
            $this->clearCache($manager);
        }
        return $manager;
    }

    private function rememberInCache(int|string $identifier, string $suffix, callable $callback): ?Manager
    {
        $key = $this->makeCacheKey($identifier, $suffix);

        return $this->getCacheRepository()->remember(
            $key,
            $this->getCacheTtlInSeconds(),
            $callback
        );
    }

    protected function clearCache(Manager $manager): void
    {
        $this->getCacheRepository()->forget($this->makeCacheKey($manager->id, self::KEY_SUFFIX_MANAGER_ID));
        $this->getCacheRepository()->forget($this->makeCacheKey($manager->esanj_id, self::KEY_SUFFIX_ESANJ_ID));
    }

    private function makeCacheKey(int|string $id, string $suffix): string
    {
        return $this->getCachePrefix() . $id . $suffix;
    }

    private function getCacheTtlInSeconds(): int
    {
        return config('manager.cache.is_enabled', true)
            ? config('manager.cache.ttl', 60)
            : 1;
    }

    private function getCachePrefix(): string
    {
        return config('manager.cache.prefix', 'manager_');
    }

    private function getCacheRepository(): CacheRepository
    {
        $driver = config('manager.cache.driver', config('cache.default'));
        return Cache::driver($driver);
    }
}
