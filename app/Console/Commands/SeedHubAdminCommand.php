<?php

namespace App\Console\Commands;

use Database\Seeders\HubAdminSeeder;
use Illuminate\Console\Command;

class SeedHubAdminCommand extends Command
{
    protected $signature = 'voltrune:seed-hub-admin {--force : Force the operation to run when in production}';

    protected $description = 'Create or update the initial Hub admin user and owner company';

    public function handle(): int
    {
        return $this->call('db:seed', [
            '--class' => HubAdminSeeder::class,
            '--force' => (bool) $this->option('force'),
        ]);
    }
}
