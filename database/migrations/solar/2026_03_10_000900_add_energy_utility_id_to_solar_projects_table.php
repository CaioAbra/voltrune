<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('solar_mysql')->table('solar_projects', function (Blueprint $table): void {
            $table->foreignId('energy_utility_id')
                ->nullable()
                ->after('utility_company')
                ->constrained('energy_utilities')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::connection('solar_mysql')->table('solar_projects', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('energy_utility_id');
        });
    }
};
