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
            $table->string('connection_type', 20)->nullable()->after('energy_bill_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('solar_mysql')->table('solar_projects', function (Blueprint $table): void {
            $table->dropColumn('connection_type');
        });
    }
};
