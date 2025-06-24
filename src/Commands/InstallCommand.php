<?php

namespace Esanj\Manager\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'manager:install';
    protected $description = 'Install the Esanj Manager package (migrate, publish config and assets)';

    public function handle(): int
    {
        $this->info('Publishing configuration...');
        $this->call('vendor:publish', [
            '--provider' => "Esanj\\Manager\\Providers\\ManagerServiceProvider",
            '--tag' => ['manager-config', 'manager-assets'],
            '--force' => true,
        ]);

        $this->info('Running migrations...');
        $this->call('migrate');

        $this->info('Manager package installed successfully ✔');
        return self::SUCCESS;
    }
}
