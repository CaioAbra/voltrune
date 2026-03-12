<?php

namespace App\Console\Commands;

use App\Modules\Solar\Models\SolarCompanySetting;
use App\Modules\Solar\Models\SolarProject;
use App\Modules\Solar\Services\SolarSimulationService;
use Illuminate\Console\Command;

class BackfillSolarProjectSimulationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'solar:backfill-project-simulations {--project_id=* : IDs especificos de projeto}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria ou sincroniza a simulacao padrao para projetos solares ja existentes.';

    public function __construct(
        private readonly SolarSimulationService $simulations,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $projectIds = collect((array) $this->option('project_id'))
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->values();

        $query = SolarProject::query()->orderBy('id');

        if ($projectIds->isNotEmpty()) {
            $query->whereIn('id', $projectIds->all());
        }

        $processed = 0;

        $query->chunkById(100, function ($projects) use (&$processed): void {
            foreach ($projects as $project) {
                $companySetting = SolarCompanySetting::query()
                    ->where('company_id', $project->company_id)
                    ->first();

                $this->simulations->syncDefaultForProject($project, $companySetting);
                $processed++;
            }
        });

        $this->info("Simulacoes sincronizadas: {$processed}");

        return self::SUCCESS;
    }
}
