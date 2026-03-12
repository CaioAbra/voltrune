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
        Schema::connection('solar_mysql')->table('solar_quotes', function (Blueprint $table): void {
            $table->foreignId('solar_simulation_id')
                ->nullable()
                ->after('solar_project_id')
                ->constrained('solar_simulations')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('solar_mysql')->table('solar_quotes', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('solar_simulation_id');
        });
    }
};
