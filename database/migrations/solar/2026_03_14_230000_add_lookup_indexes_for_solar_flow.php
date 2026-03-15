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
        Schema::connection('solar_mysql')->table('solar_customers', function (Blueprint $table): void {
            $table->index(['company_id', 'name'], 'solar_customers_company_name_index');
        });

        Schema::connection('solar_mysql')->table('solar_projects', function (Blueprint $table): void {
            $table->index(['company_id', 'status'], 'solar_projects_company_status_index');
            $table->index(['company_id', 'name'], 'solar_projects_company_name_index');
        });

        Schema::connection('solar_mysql')->table('solar_simulations', function (Blueprint $table): void {
            $table->index(['company_id', 'status'], 'solar_simulations_company_status_index');
            $table->index(['company_id', 'created_at'], 'solar_simulations_company_created_at_index');
        });

        Schema::connection('solar_mysql')->table('solar_quotes', function (Blueprint $table): void {
            $table->index(['company_id', 'status'], 'solar_quotes_company_status_index');
            $table->index(['company_id', 'created_at'], 'solar_quotes_company_created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('solar_mysql')->table('solar_quotes', function (Blueprint $table): void {
            $table->dropIndex('solar_quotes_company_created_at_index');
            $table->dropIndex('solar_quotes_company_status_index');
        });

        Schema::connection('solar_mysql')->table('solar_simulations', function (Blueprint $table): void {
            $table->dropIndex('solar_simulations_company_created_at_index');
            $table->dropIndex('solar_simulations_company_status_index');
        });

        Schema::connection('solar_mysql')->table('solar_projects', function (Blueprint $table): void {
            $table->dropIndex('solar_projects_company_name_index');
            $table->dropIndex('solar_projects_company_status_index');
        });

        Schema::connection('solar_mysql')->table('solar_customers', function (Blueprint $table): void {
            $table->dropIndex('solar_customers_company_name_index');
        });
    }
};
