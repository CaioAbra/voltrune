<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('solar_mysql')->table('solar_projects', function (Blueprint $table): void {
            $table->decimal('system_power_kwp', 10, 2)->nullable()->after('connection_type');
            $table->unsignedInteger('module_power')->nullable()->after('system_power_kwp');
            $table->unsignedInteger('module_quantity')->nullable()->after('module_power');
            $table->string('inverter_model')->nullable()->after('module_quantity');
            $table->decimal('estimated_generation_kwh', 10, 2)->nullable()->after('inverter_model');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('solar_mysql')->table('solar_projects', function (Blueprint $table): void {
            $table->dropColumn([
                'system_power_kwp',
                'module_power',
                'module_quantity',
                'inverter_model',
                'estimated_generation_kwh',
            ]);
        });
    }
};
