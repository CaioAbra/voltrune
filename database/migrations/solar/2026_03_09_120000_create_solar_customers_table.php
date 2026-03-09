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
        Schema::connection('solar_mysql')->create('solar_customers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('name');
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();
            $table->string('document')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('solar_mysql')->dropIfExists('solar_customers');
    }
};
