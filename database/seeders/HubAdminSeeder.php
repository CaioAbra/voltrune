<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HubAdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminName = (string) env('VOLTRUNE_ADMIN_NAME', 'Voltrune Admin');
        $adminEmail = (string) env('VOLTRUNE_ADMIN_EMAIL', 'admin@voltrune.com');
        $adminPassword = (string) env('VOLTRUNE_ADMIN_PASSWORD', '');
        $companyName = (string) env('VOLTRUNE_ADMIN_COMPANY_NAME', 'Voltrune');
        $companySlug = (string) env('VOLTRUNE_ADMIN_COMPANY_SLUG', 'voltrune');
        $companyStatus = (string) env('VOLTRUNE_ADMIN_COMPANY_STATUS', 'active');

        if ($adminPassword === '') {
            $this->command?->warn('VOLTRUNE_ADMIN_PASSWORD vazio. Defina no .env para criar o admin.');

            return;
        }

        DB::connection('hub_mysql')->transaction(function () use (
            $adminName,
            $adminEmail,
            $adminPassword,
            $companyName,
            $companySlug,
            $companyStatus
        ): void {
            $user = User::query()->updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => $adminName,
                    'password' => $adminPassword,
                ]
            );

            $company = Company::query()->firstOrCreate(
                ['slug' => $companySlug],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => $companyName,
                    'status' => $companyStatus,
                ]
            );

            $company->status = $companyStatus;
            $company->save();

            $company->users()->syncWithoutDetaching([
                $user->id => [
                    'role' => 'owner',
                    'is_owner' => true,
                ],
            ]);
        });

        $this->command?->info("Admin do Hub pronto: {$adminEmail}");
    }
}
