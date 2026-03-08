<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateHubCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'voltrune:migrate-hub {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run only Hub migrations using the hub_mysql connection';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->components->info('Running Hub migrations...');

        return $this->call('migrate', [
            '--database' => 'hub_mysql',
            '--path' => 'database/migrations/hub',
            '--force' => (bool) $this->option('force'),
        ]);
    }
}
