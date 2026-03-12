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
        Schema::connection('solar_mysql')->create('solar_simulations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->foreignId('solar_project_id')
                ->constrained('solar_projects')
                ->cascadeOnDelete();
            $table->string('name');
            $table->decimal('system_power_kwp', 10, 2)->nullable();
            $table->unsignedInteger('module_power')->nullable();
            $table->unsignedInteger('module_quantity')->nullable();
            $table->decimal('estimated_generation_kwh', 12, 2)->nullable();
            $table->decimal('area_estimated', 10, 2)->nullable();
            $table->string('inverter_model')->nullable();
            $table->decimal('solar_factor_used', 10, 2)->nullable();
            $table->string('solar_factor_source')->nullable();
            $table->decimal('suggested_price', 12, 2)->nullable();
            $table->decimal('estimated_monthly_savings', 12, 2)->nullable();
            $table->decimal('estimated_annual_savings', 12, 2)->nullable();
            $table->decimal('estimated_lifetime_savings', 14, 2)->nullable();
            $table->decimal('estimated_roi', 8, 2)->nullable();
            $table->unsignedInteger('estimated_payback_months')->nullable();
            $table->json('system_composition_json')->nullable();
            $table->string('status')->default('draft')->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['solar_project_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('solar_mysql')->dropIfExists('solar_simulations');
    }
};
