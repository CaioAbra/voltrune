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
        Schema::connection('solar_mysql')->create('solar_quotes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->foreignId('solar_project_id')
                ->constrained('solar_projects')
                ->cascadeOnDelete();
            $table->string('title');
            $table->decimal('total_value', 12, 2)->default(0);
            $table->decimal('estimated_savings', 12, 2)->nullable();
            $table->unsignedInteger('payback_months')->nullable();
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
        Schema::connection('solar_mysql')->dropIfExists('solar_quotes');
    }
};
