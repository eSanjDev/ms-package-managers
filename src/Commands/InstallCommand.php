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


        $this->ensureEnvKeys([
            'MANAGER_SUCCESS_REDIRECT',
        ]);


        $this->info('Manager package installed successfully ✔');
        return self::SUCCESS;
    }

    protected function ensureEnvKeys(array $keys): bool
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath) || !is_writable($envPath)) {
            $this->error('.env file not found or not writable.');
            return false;
        }

        $envContent = file_get_contents($envPath);
        $lines = explode("\n", $envContent);

        $existingKeys = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $existingKeys[trim($parts[0])] = true;
            }
        }

        $newLines = [];
        foreach ($keys as $key) {
            if (!isset($existingKeys[$key])) {
                $newLines[] = "$key=";
            }
        }

        if (!empty($newLines)) {
            $envContent = rtrim($envContent) . "\n" . implode("\n", $newLines) . "\n";
            file_put_contents($envPath, $envContent);
            $this->info('.env keys added: ' . implode(', ', $keys));
        } else {
            $this->info('All env keys already exist.');
        }

        return true;
    }
}
