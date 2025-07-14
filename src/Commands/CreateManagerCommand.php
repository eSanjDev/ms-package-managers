<?php

namespace Esanj\Manager\Commands;

use Esanj\Manager\Enums\ManagerRoleEnum;
use Esanj\Manager\Services\ManagerService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateManagerCommand extends Command
{
    protected $signature = 'manager:create';
    protected $description = 'Create a new manager (admin) with static token';

    public function handle(ManagerService $service): int
    {
        $esanjId = $this->ask('Esanj ID');

        if (!$esanjId) {
            $this->error('Esanj id are required.');
            return self::FAILURE;
        }

        $manager = $service->findByEsanjId($esanjId);

        if ($manager) {
            $this->error('Manager with this ID already exists.');
            return self::FAILURE;
        }

        $token = Str::random(32);

        $data = [
            'esanj_id' => $esanjId,
            'token' => $token,
            'role' => ManagerRoleEnum::Admin,
        ];
        $service->createManager($data);

        $this->info("Token: $token");

        $this->info('Manager successfully created!');
        return self::SUCCESS;
    }
}
