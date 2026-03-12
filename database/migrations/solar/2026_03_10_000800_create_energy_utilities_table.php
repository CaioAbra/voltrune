<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('solar_mysql')->create('energy_utilities', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('state', 2)->index();
            $table->json('cities_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('solar_mysql')->dropIfExists('energy_utilities');
    }
};
