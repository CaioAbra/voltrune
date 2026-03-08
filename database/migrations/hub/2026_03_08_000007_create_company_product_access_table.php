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
        Schema::connection('hub_mysql')->create('company_product_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('product_key');
            $table->enum('access_status', ['active', 'inactive'])->default('inactive');
            $table->timestamps();

            $table->unique(['company_id', 'product_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('hub_mysql')->dropIfExists('company_product_access');
    }
};
