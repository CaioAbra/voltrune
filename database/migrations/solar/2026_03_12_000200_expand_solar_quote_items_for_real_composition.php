<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('solar_mysql')->table('solar_quote_items', function (Blueprint $table): void {
            $table->string('type')->default('material')->after('solar_quote_id');
            $table->string('category')->default('other')->after('type');
            $table->decimal('unit_cost', 12, 2)->default(0)->after('quantity');
            $table->decimal('total_cost', 12, 2)->default(0)->after('unit_price');
        });

        DB::connection('solar_mysql')->table('solar_quote_items')->update([
            'type' => 'material',
            'category' => 'other',
            'unit_cost' => 0,
            'total_cost' => DB::raw('total_price'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('solar_mysql')->table('solar_quote_items', function (Blueprint $table): void {
            $table->dropColumn([
                'type',
                'category',
                'unit_cost',
                'total_cost',
            ]);
        });
    }
};
