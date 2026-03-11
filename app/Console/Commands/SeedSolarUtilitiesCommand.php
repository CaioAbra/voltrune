<?php

namespace App\Console\Commands;

use Database\Seeders\SolarEnergyUtilitySeeder;
use Illuminate\Console\Command;

class SeedSolarUtilitiesCommand extends Command
{
    protected $signature = 'voltrune:seed-solar-utilities
        {--force : Force the operation to run when in production}
        {--fallback-only : Skip national sync and use local fallback seeder only}
        {--prune : Remove concessionarias antigas nao encontradas na base nacional}';

    protected $description = 'Seed the Solar energy utilities catalog on the solar_mysql connection';

    public function handle(): int
    {
        if (! (bool) $this->option('fallback-only')) {
            $exitCode = $this->call('voltrune:sync-solar-utilities-national', [
                '--prune' => (bool) $this->option('prune'),
            ]);

            if ($exitCode === self::SUCCESS) {
                return self::SUCCESS;
            }

            $this->components->warn('Falha na sincronizacao nacional. Aplicando fallback local.');
        }

        return $this->call('db:seed', [
            '--class' => SolarEnergyUtilitySeeder::class,
            '--database' => 'solar_mysql',
            '--force' => (bool) $this->option('force'),
        ]);
    }
}
