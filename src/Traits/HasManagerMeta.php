<?php

namespace Esanj\Manager\Traits;

use Esanj\Manager\Models\ManagerMeta;

trait HasManagerMeta
{
    public function getMeta($key)
    {
        return $this->meta->where('key', $key)->first();
    }

    public function meta()
    {
        return $this->hasMany(ManagerMeta::class);
    }

    public function setMeta(string $key, $value)
    {
        return $this->meta()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
