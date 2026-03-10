<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('solar_mysql')->table('solar_projects', function (Blueprint $table): void {
            $table->decimal('suggested_price', 12, 2)->nullable()->after('estimated_generation_kwh');
            $table->text('pricing_notes')->nullable()->after('suggested_price');
        });
    }

    public function down(): void
    {
        Schema::connection('solar_mysql')->table('solar_projects', function (Blueprint $table): void {
            $table->dropColumn(['suggested_price', 'pricing_notes']);
        });
    }
};
