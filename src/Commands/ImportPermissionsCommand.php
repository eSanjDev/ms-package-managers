<?php

namespace Esanj\Manager\Commands;

use Esanj\Manager\Models\Permission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ImportPermissionsCommand extends Command
{
    protected $signature = 'manager:permissions-import';
    protected $description = 'Import permissions for the Manager package';

    public function handle(): int
    {
        if (!Schema::hasTable('permissions')) {
            $this->error('Table "permissions" does not exist.');
            $this->info('Run "php artisan migrate" first.');
            return self::FAILURE;
        }

        $permissions = config('esanj.manager.permissions', []);

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

        $stale = Permission::query()
            ->whereNotIn('key', array_keys($permissions))
            ->pluck('key');

        if ($stale->isNotEmpty()) {
            $this->warn('Permissions in the database that are no longer in the config (still assignable):');
            foreach ($stale as $key) {
                $this->line("  - {$key}");
            }
            $this->warn('Delete them manually if they should no longer exist.');
        }

        return self::SUCCESS;
    }
}
