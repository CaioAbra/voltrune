<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('solar_mysql')->create('solar_catalog_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('type')->default('material')->index();
            $table->string('category')->default('other')->index();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('sku')->nullable()->index();
            $table->string('supplier')->nullable();
            $table->string('unit_label')->default('un');
            $table->decimal('default_quantity', 10, 2)->default(1);
            $table->decimal('default_cost', 12, 2)->default(0);
            $table->decimal('default_price', 12, 2)->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::connection('solar_mysql')->dropIfExists('solar_catalog_items');
    }
};
