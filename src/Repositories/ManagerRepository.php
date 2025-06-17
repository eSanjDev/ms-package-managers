<?php

namespace Esanj\Manager\Repositories;

use Esanj\Manager\Models\Manager;
use Illuminate\Support\Facades\Cache;

class ManagerRepository
{
    protected mixed $cache_driver;
    protected string $cache_prefix;
    protected int $cache_ttl;

    public function __construct(protected Manager $model)
    {
        $this->cache_driver = config('manager.cache.driver');
        $this->cache_ttl = config('manager.cache.is_enabled', true) ? config('manager.cache.ttl') : 0;
        $this->cache_prefix = config('manager.cache.prefix', 'manager');
    }

    public function findByMangerId(int $id)
    {
        return Cache::driver($this->cache_driver)->remember($this->cache_prefix . $id, $this->cache_ttl, function () use ($id) {
            return $this->model->where(['manager_id' => $id])->first();
        });
    }

    protected function clearManagerCache(int $managerID)
    {
        return Cache::driver($this->cache_driver)->forget($this->cache_prefix . $managerID);
    }

    public function findById(int $id)
    {
        return $this->model->find($id);
    }

    public function update(int $id, array $data)
    {
        $manager = $this->findById($id);
        if (!$manager) return null;
        $manager->update($data);
        $this->clearManagerCache($manager->manager_id);
        return $manager;
    }

    public function create(array $data)
    {
        $manager = $this->model->create($data);
        $this->clearManagerCache($manager->manager_id);
        return $manager;
    }


}
