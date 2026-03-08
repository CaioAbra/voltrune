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
        Schema::connection('hub_mysql')->create('company_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('product_key');
            $table->boolean('is_active')->default(false);
            $table->string('plan_name')->nullable();
            $table->enum('billing_cycle', ['monthly', 'yearly', 'custom'])->nullable();
            $table->decimal('negotiated_value', 10, 2)->nullable();
            $table->text('contract_notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'product_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('hub_mysql')->dropIfExists('company_products');
    }
};
