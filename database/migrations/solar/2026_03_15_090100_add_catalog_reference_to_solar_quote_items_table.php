<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('solar_mysql')->table('solar_quote_items', function (Blueprint $table): void {
            $table->foreignId('solar_catalog_item_id')
                ->nullable()
                ->after('solar_quote_id')
                ->constrained('solar_catalog_items')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::connection('solar_mysql')->table('solar_quote_items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('solar_catalog_item_id');
        });
    }
};
