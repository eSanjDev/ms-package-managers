<?php

namespace Esanj\Manager\Commands;

use Esanj\Manager\Services\ManagerService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateManagerCommand extends Command
{
    protected $signature = 'manager:create';
    protected $description = 'Create a new manager (admin) with static token';

    public function handle(ManagerService $service): int
    {
        $managerId = $this->ask('Manager ID');

        if (!$managerId) {
            $this->error('manager id are required.');
            return self::FAILURE;
        }

        $manager = $service->findByManagerID($managerId);
        if ($manager) {
            $this->error('Manager with this ID already exists.');
            return self::FAILURE;
        }

        $service->createManager($managerId, Str::random(32));

        $this->info('Manager successfully created!');
        return self::SUCCESS;
    }
}
