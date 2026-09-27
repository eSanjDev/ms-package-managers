<?php

namespace Esanj\Manager\Commands;

use Esanj\Manager\Enums\ManagerRoleEnum;
use Esanj\Manager\Services\ManagerService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateManagerCommand extends Command
{
    protected $signature = 'manager:create {esanj_id?}';
    protected $description = 'Create a new manager (admin) with static token';

    public function handle(ManagerService $service): int
    {
        $esanjId = $this->argument('esanj_id') ?? $this->ask('Esanj ID');

        if (!ctype_digit((string) $esanjId) || (int) $esanjId < 1) {
            $this->error('The Esanj ID must be a positive integer.');
            return self::FAILURE;
        }

        $esanjId = (int) $esanjId;
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
