<?php

namespace Esanj\Manager\Commands;

use Esanj\Manager\Models\Permission;
use Illuminate\Console\Command;

class ImportPermissionsCommand extends Command
{
    protected $signature = 'manager:permissions-import';
    protected $description = 'Import permissions for the Manager package';

    public function handle(): int
    {
        $permissions = config('esanj.manager.permissions');

        foreach ($permissions as $key => $item) {

            Permission::updateOrCreate(
                [
                    'key' => $key
                ],
                [
                    'display_name' => $item['display_name'] ?? '',
                    'description' => $item['description'] ?? '',
                ]);
        }


        $this->info('Permissions imported successfully ✔');
        return self::SUCCESS;
    }
}
