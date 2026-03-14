<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('solar_mysql')->table('solar_quotes', function (Blueprint $table): void {
            $table->json('simulation_snapshot_json')->nullable()->after('solar_simulation_id');
        });
    }

    public function down(): void
    {
        Schema::connection('solar_mysql')->table('solar_quotes', function (Blueprint $table): void {
            $table->dropColumn('simulation_snapshot_json');
        });
    }
};
