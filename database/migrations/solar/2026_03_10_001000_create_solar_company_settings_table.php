<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('solar_mysql')->create('solar_company_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id')->unique();
            $table->unsignedInteger('default_module_power')->nullable();
            $table->decimal('price_per_kwp', 12, 2)->nullable();
            $table->decimal('margin_percent', 5, 2)->nullable();
            $table->string('default_inverter_model')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('solar_mysql')->dropIfExists('solar_company_settings');
    }
};
