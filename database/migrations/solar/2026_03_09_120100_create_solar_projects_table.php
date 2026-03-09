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
        Schema::connection('solar_mysql')->create('solar_projects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->foreignId('solar_customer_id')
                ->constrained('solar_customers')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('address')->nullable();
            $table->decimal('monthly_consumption_kwh', 10, 2)->nullable();
            $table->decimal('energy_bill_value', 12, 2)->nullable();
            $table->string('status')->default('draft')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('solar_mysql')->dropIfExists('solar_projects');
    }
};
