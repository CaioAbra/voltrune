<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('solar_mysql')->table('solar_projects', function (Blueprint $table): void {
            $table->decimal('solar_factor_used', 10, 2)->nullable()->after('estimated_generation_kwh');
            $table->string('solar_factor_source', 30)->nullable()->after('solar_factor_used');
            $table->timestamp('solar_factor_fetched_at')->nullable()->after('solar_factor_source');
            $table->string('radiation_status', 30)->nullable()->after('solar_factor_fetched_at')->index();
        });
    }

    public function down(): void
    {
        Schema::connection('solar_mysql')->table('solar_projects', function (Blueprint $table): void {
            $table->dropColumn([
                'solar_factor_used',
                'solar_factor_source',
                'solar_factor_fetched_at',
                'radiation_status',
            ]);
        });
    }
};
