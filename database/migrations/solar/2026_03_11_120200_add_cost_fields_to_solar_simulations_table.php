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
        Schema::connection('solar_mysql')->table('solar_simulations', function (Blueprint $table): void {
            $table->decimal('estimated_module_cost', 12, 2)->nullable()->after('suggested_price');
            $table->decimal('estimated_inverter_cost', 12, 2)->nullable()->after('estimated_module_cost');
            $table->decimal('estimated_structure_cost', 12, 2)->nullable()->after('estimated_inverter_cost');
            $table->decimal('estimated_installation_cost', 12, 2)->nullable()->after('estimated_structure_cost');
            $table->decimal('estimated_kit_cost', 12, 2)->nullable()->after('estimated_installation_cost');
            $table->decimal('estimated_gross_profit', 12, 2)->nullable()->after('estimated_kit_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('solar_mysql')->table('solar_simulations', function (Blueprint $table): void {
            $table->dropColumn([
                'estimated_module_cost',
                'estimated_inverter_cost',
                'estimated_structure_cost',
                'estimated_installation_cost',
                'estimated_kit_cost',
                'estimated_gross_profit',
            ]);
        });
    }
};
