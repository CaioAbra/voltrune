<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('solar_mysql')->table('solar_company_settings', function (Blueprint $table): void {
            $table->string('margin_mode', 20)->default('fixed');
        });

        Schema::connection('solar_mysql')->create('solar_company_margin_ranges', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('solar_company_setting_id')
                ->constrained('solar_company_settings')
                ->cascadeOnDelete();
            $table->decimal('min_kwp', 8, 2);
            $table->decimal('max_kwp', 8, 2)->nullable();
            $table->decimal('margin_percent', 5, 2)->nullable();
            $table->boolean('requires_negotiation')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('solar_mysql')->dropIfExists('solar_company_margin_ranges');

        Schema::connection('solar_mysql')->table('solar_company_settings', function (Blueprint $table): void {
            $table->dropColumn('margin_mode');
        });
    }
};
