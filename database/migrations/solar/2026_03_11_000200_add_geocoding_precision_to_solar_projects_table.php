<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('solar_mysql')->table('solar_projects', function (Blueprint $table): void {
            $table->string('geocoding_precision', 20)->default('fallback')->after('geocoding_status')->index();
        });
    }

    public function down(): void
    {
        Schema::connection('solar_mysql')->table('solar_projects', function (Blueprint $table): void {
            $table->dropColumn('geocoding_precision');
        });
    }
};
