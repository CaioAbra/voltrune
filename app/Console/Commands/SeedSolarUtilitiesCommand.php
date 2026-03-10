<?php

namespace App\Console\Commands;

use Database\Seeders\SolarEnergyUtilitySeeder;
use Illuminate\Console\Command;

class SeedSolarUtilitiesCommand extends Command
{
    protected $signature = 'voltrune:seed-solar-utilities {--force : Force the operation to run when in production}';

    protected $description = 'Seed the Solar energy utilities catalog on the solar_mysql connection';

    public function handle(): int
    {
        return $this->call('db:seed', [
            '--class' => SolarEnergyUtilitySeeder::class,
            '--database' => 'solar_mysql',
            '--force' => (bool) $this->option('force'),
        ]);
    }
}
