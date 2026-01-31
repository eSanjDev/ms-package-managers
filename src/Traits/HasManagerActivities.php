<?php

namespace Esanj\Manager\Traits;

use Esanj\Manager\Models\ManagerActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasManagerActivities
{
    public function activities(): HasMany
    {
        return $this->hasMany(ManagerActivity::class)->latest();
    }

    public function setActivity(string $type, array $meta = [])
    {
        return $this->activities()->create([
            'type' => $type,
            'meta' => $meta
        ]);
    }
}
