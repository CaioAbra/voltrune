<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateSolarCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'voltrune:migrate-solar {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run only Solar migrations using the solar_mysql connection';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->components->info('Running Solar migrations...');

        return $this->call('migrate', [
            '--database' => 'solar_mysql',
            '--path' => 'database/migrations/solar',
            '--force' => (bool) $this->option('force'),
        ]);
    }
}
