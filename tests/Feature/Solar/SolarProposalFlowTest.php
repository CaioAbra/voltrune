<?php

namespace Tests\Feature\Solar;

use App\Models\Company;
use App\Models\User;
use App\Http\Middleware\EnsureCompanyIsActive;
use App\Http\Middleware\EnsureProductAccessIsActive;
use App\Modules\Solar\Models\SolarCatalogItem;
use App\Modules\Solar\Models\SolarCustomer;
use App\Modules\Solar\Models\SolarProject;
use App\Modules\Solar\Models\SolarQuote;
use App\Modules\Solar\Models\SolarSimulation;
use App\Support\CurrentCompanyContext;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SolarProposalFlowTest extends TestCase
{
    private string $hubDatabasePath;

    private string $solarDatabasePath;

    protected function setUp(): void
    {
        parent::setUp();

        $testingPath = storage_path('framework/testing');
        File::ensureDirectoryExists($testingPath);

        $this->hubDatabasePath = $testingPath . '/hub-solar-feature.sqlite';
        $this->solarDatabasePath = $testingPath . '/solar-solar-feature.sqlite';

        File::delete($this->hubDatabasePath);
        File::delete($this->solarDatabasePath);
        File::put($this->hubDatabasePath, '');
        File::put($this->solarDatabasePath, '');

        config()->set('database.connections.hub_mysql', [
            'driver' => 'sqlite',
            'database' => $this->hubDatabasePath,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        config()->set('database.connections.solar_mysql', [
            'driver' => 'sqlite',
            'database' => $this->solarDatabasePath,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        DB::purge('hub_mysql');
        DB::purge('solar_mysql');

        foreach ([
            '2026_03_07_000000_create_companies_table.php',
            '2026_03_07_000001_create_users_table.php',
            '2026_03_07_000002_create_company_user_table.php',
        ] as $hubMigration) {
            Artisan::call('migrate', [
                '--database' => 'hub_mysql',
                '--path' => base_path('database/migrations/hub/' . $hubMigration),
                '--realpath' => true,
                '--force' => true,
            ]);
        }
        Artisan::call('migrate:fresh', [
            '--database' => 'solar_mysql',
            '--path' => base_path('database/migrations/solar'),
            '--realpath' => true,
            '--force' => true,
        ]);

        $this->resetCompanyContextCache();
        $this->withoutMiddleware([
            EnsureCompanyIsActive::class,
            EnsureProductAccessIsActive::class,
        ]);
    }

    protected function tearDown(): void
    {
        DB::disconnect('hub_mysql');
        DB::disconnect('solar_mysql');
        File::delete($this->hubDatabasePath);
        File::delete($this->solarDatabasePath);

        parent::tearDown();
    }

    public function test_project_show_displays_simulation_comparison_section(): void
    {
        [$company, $user] = $this->authenticatedHubContext();
        $customer = SolarCustomer::query()->create([
            'company_id' => $company->id,
            'name' => 'Cliente Teste',
        ]);
        $project = SolarProject::query()->create([
            'company_id' => $company->id,
            'solar_customer_id' => $customer->id,
            'name' => 'Projeto comparativo',
            'city' => 'Sao Paulo',
            'state' => 'SP',
            'monthly_consumption_kwh' => 640,
            'energy_bill_value' => 520,
            'status' => 'proposal',
        ]);

        $simulationA = SolarSimulation::query()->create([
            'company_id' => $company->id,
            'solar_project_id' => $project->id,
            'name' => 'Leitura inicial',
            'system_power_kwp' => 4.8,
            'estimated_generation_kwh' => 670,
            'suggested_price' => 21400,
            'estimated_monthly_savings' => 430,
            'estimated_payback_months' => 52,
            'status' => 'proposal',
        ]);
        $simulationB = SolarSimulation::query()->create([
            'company_id' => $company->id,
            'solar_project_id' => $project->id,
            'name' => 'Revisao 01',
            'system_power_kwp' => 5.2,
            'estimated_generation_kwh' => 720,
            'suggested_price' => 22650,
            'estimated_monthly_savings' => 470,
            'estimated_payback_months' => 48,
            'status' => 'proposal',
        ]);

        SolarQuote::query()->create([
            'company_id' => $company->id,
            'solar_project_id' => $project->id,
            'solar_simulation_id' => $simulationB->id,
            'proposal_code' => 'SOL-1-20260315-ABCD-V01',
            'version_group_code' => 'SOL-1-20260315-ABCD',
            'version_number' => 1,
            'title' => 'Orcamento solar - Cliente Teste',
            'final_price' => 22650,
            'total_value' => 22650,
            'estimated_savings' => 470,
            'payback_months' => 48,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)
            ->withSession([CurrentCompanyContext::SESSION_KEY => $company->id])
            ->get(route('solar.projects.show', $project->id));

        $response->assertOk();
        $response->assertSee('Comparacao guiada');
        $response->assertSee('Melhor retorno');
        $response->assertSee('Leitura inicial');
        $response->assertSee('Revisao 01');
        $response->assertSee('Maior economia mensal');
    }

    public function test_quote_proposal_view_displays_code_versions_and_history(): void
    {
        [$company, $user] = $this->authenticatedHubContext();
        $customer = SolarCustomer::query()->create([
            'company_id' => $company->id,
            'name' => 'Cliente Proposta',
        ]);
        $project = SolarProject::query()->create([
            'company_id' => $company->id,
            'solar_customer_id' => $customer->id,
            'name' => 'Projeto proposta',
            'city' => 'Campinas',
            'state' => 'SP',
            'monthly_consumption_kwh' => 510,
            'energy_bill_value' => 430,
            'status' => 'proposal',
        ]);
        $simulation = SolarSimulation::query()->create([
            'company_id' => $company->id,
            'solar_project_id' => $project->id,
            'name' => 'Leitura inicial',
            'system_power_kwp' => 4.2,
            'estimated_generation_kwh' => 580,
            'suggested_price' => 19800,
            'estimated_monthly_savings' => 390,
            'estimated_payback_months' => 51,
            'status' => 'proposal',
        ]);
        $quote = SolarQuote::query()->create([
            'company_id' => $company->id,
            'solar_project_id' => $project->id,
            'solar_simulation_id' => $simulation->id,
            'simulation_snapshot_json' => [
                'name' => 'Leitura inicial',
                'system_power_kwp' => 4.2,
                'estimated_generation_kwh' => 580,
                'suggested_price' => 19800,
                'estimated_payback_months' => 51,
            ],
            'proposal_code' => 'SOL-1-20260315-ZXCV-V01',
            'version_group_code' => 'SOL-1-20260315-ZXCV',
            'version_number' => 1,
            'title' => 'Orcamento solar - Cliente Proposta',
            'final_price' => 19800,
            'total_value' => 19800,
            'estimated_savings' => 390,
            'payback_months' => 51,
            'status' => 'sent',
            'sent_at' => '2026-03-15 11:45:00',
            'notes' => 'Entrega pronta para proposta comercial.',
        ]);
        SolarQuote::query()->create([
            'company_id' => $company->id,
            'solar_project_id' => $project->id,
            'solar_simulation_id' => $simulation->id,
            'proposal_code' => 'SOL-1-20260315-ZXCV-V02',
            'version_group_code' => 'SOL-1-20260315-ZXCV',
            'version_number' => 2,
            'source_quote_id' => $quote->id,
            'title' => 'Orcamento solar - Cliente Proposta | V02',
            'final_price' => 20500,
            'total_value' => 20500,
            'estimated_savings' => 405,
            'payback_months' => 50,
            'status' => 'draft',
        ]);
        $quote->items()->create([
            'type' => 'material',
            'category' => 'module',
            'name' => 'Painel 550W',
            'description' => 'Modulo de alta eficiencia',
            'quantity' => 8,
            'unit_cost' => 650,
            'unit_price' => 900,
            'total_cost' => 5200,
            'total_price' => 7200,
        ]);
        $quote->events()->create([
            'company_id' => $company->id,
            'event_type' => 'quote_created',
            'title' => 'Versao inicial registrada',
            'description' => 'Orcamento criado a partir da simulacao base.',
        ]);

        $response = $this->actingAs($user)
            ->withSession([CurrentCompanyContext::SESSION_KEY => $company->id])
            ->get(route('solar.quotes.proposal', $quote->id));

        $response->assertOk();
        $response->assertSee('Proposta comercial');
        $response->assertSee('SOL-1-20260315-ZXCV-V01');
        $response->assertSee('Versao 01');
        $response->assertSee('Versao 02');
        $response->assertSee('Painel 550W');
        $response->assertSee('Versao inicial registrada');
    }

    public function test_catalog_index_displays_operational_items(): void
    {
        [$company, $user] = $this->authenticatedHubContext();

        SolarCatalogItem::query()->create([
            'company_id' => $company->id,
            'type' => 'material',
            'category' => 'module',
            'name' => 'Painel N-Type 585W',
            'brand' => 'Voltrune',
            'sku' => 'MOD-585',
            'supplier' => 'Distribuidor Centro',
            'unit_label' => 'un',
            'default_quantity' => 1,
            'default_cost' => 640,
            'default_price' => 890,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->withSession([CurrentCompanyContext::SESSION_KEY => $company->id])
            ->get(route('solar.catalog.index'));

        $response->assertOk();
        $response->assertSee('Catalogo ativo');
        $response->assertSee('Painel N-Type 585W');
        $response->assertSee('Distribuidor Centro');
    }

    public function test_quote_can_add_item_from_catalog(): void
    {
        [$company, $user] = $this->authenticatedHubContext();
        $customer = SolarCustomer::query()->create([
            'company_id' => $company->id,
            'name' => 'Cliente Catalogo',
        ]);
        $project = SolarProject::query()->create([
            'company_id' => $company->id,
            'solar_customer_id' => $customer->id,
            'name' => 'Projeto com catalogo',
            'city' => 'Jundiai',
            'state' => 'SP',
            'monthly_consumption_kwh' => 720,
            'energy_bill_value' => 560,
            'status' => 'proposal',
        ]);
        $simulation = SolarSimulation::query()->create([
            'company_id' => $company->id,
            'solar_project_id' => $project->id,
            'name' => 'Leitura inicial',
            'system_power_kwp' => 5.4,
            'estimated_generation_kwh' => 710,
            'suggested_price' => 24200,
            'estimated_monthly_savings' => 490,
            'estimated_payback_months' => 50,
            'status' => 'proposal',
        ]);
        $quote = SolarQuote::query()->create([
            'company_id' => $company->id,
            'solar_project_id' => $project->id,
            'solar_simulation_id' => $simulation->id,
            'proposal_code' => 'SOL-1-20260315-CATA-V01',
            'version_group_code' => 'SOL-1-20260315-CATA',
            'version_number' => 1,
            'title' => 'Orcamento com catalogo',
            'final_price' => 24200,
            'total_value' => 24200,
            'estimated_savings' => 490,
            'payback_months' => 50,
            'status' => 'draft',
        ]);
        $catalogItem = SolarCatalogItem::query()->create([
            'company_id' => $company->id,
            'type' => 'material',
            'category' => 'module',
            'name' => 'Painel N-Type 610W',
            'brand' => 'Voltrune',
            'sku' => 'MOD-610',
            'supplier' => 'Distribuidor Sul',
            'unit_label' => 'un',
            'default_quantity' => 1,
            'default_cost' => 680,
            'default_price' => 930,
            'is_active' => true,
            'notes' => 'Modulo premium do catalogo.',
        ]);

        $response = $this->actingAs($user)
            ->withSession([CurrentCompanyContext::SESSION_KEY => $company->id])
            ->post(route('solar.quotes.items.store', $quote->id), [
                'catalog_item_id' => $catalogItem->id,
                'quantity' => 4,
            ]);

        $response->assertRedirect(route('solar.quotes.edit', $quote->id));
        $this->assertDatabaseHas('solar_quote_items', [
            'solar_quote_id' => $quote->id,
            'solar_catalog_item_id' => $catalogItem->id,
            'name' => 'Painel N-Type 610W',
            'quantity' => 4,
            'unit_cost' => 680,
            'unit_price' => 930,
            'total_cost' => 2720,
            'total_price' => 3720,
        ], 'solar_mysql');

        $quote->refresh();

        $this->assertSame('3720.00', $quote->final_price);
        $this->assertSame('3720.00', $quote->total_value);
    }

    /**
     * @return array{0: Company, 1: User}
     */
    private function authenticatedHubContext(): array
    {
        $company = Company::query()->create([
            'uuid' => '11111111-1111-1111-1111-111111111111',
            'name' => 'Voltrune Solar Test',
            'slug' => 'voltrune-solar-test',
            'status' => 'active',
        ]);
        $user = User::query()->create([
            'name' => 'Tester',
            'email' => 'tester+' . uniqid() . '@voltrune.test',
            'password' => 'secret123',
        ]);

        $user->companies()->attach($company->id, [
            'role' => 'owner',
            'is_owner' => true,
        ]);

        $this->resetCompanyContextCache();

        return [$company, $user];
    }

    private function resetCompanyContextCache(): void
    {
        $reflection = new \ReflectionClass(CurrentCompanyContext::class);

        foreach (['availableCompaniesCache', 'resolvedCompanyCache'] as $propertyName) {
            $property = $reflection->getProperty($propertyName);
            $property->setAccessible(true);
            $property->setValue([]);
        }
    }
}
