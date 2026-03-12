<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('solar_mysql')->create('solar_market_defaults', function (Blueprint $table): void {
            $table->id();
            $table->string('state', 10)->unique();
            $table->decimal('price_per_kwp', 12, 2);
            $table->decimal('module_cost_average', 12, 2);
            $table->decimal('inverter_cost_average', 12, 2);
            $table->decimal('installation_cost_average', 12, 2);
            $table->timestamps();
        });

        $now = now();
        $prices = [
            'BR' => 4200.0,
            'AC' => 4550.0,
            'AL' => 4380.0,
            'AM' => 4600.0,
            'AP' => 4580.0,
            'BA' => 4350.0,
            'CE' => 4340.0,
            'DF' => 4250.0,
            'ES' => 4280.0,
            'GO' => 4240.0,
            'MA' => 4420.0,
            'MG' => 4300.0,
            'MS' => 4230.0,
            'MT' => 4260.0,
            'PA' => 4520.0,
            'PB' => 4360.0,
            'PE' => 4370.0,
            'PI' => 4410.0,
            'PR' => 4180.0,
            'RJ' => 4320.0,
            'RN' => 4350.0,
            'RO' => 4480.0,
            'RR' => 4620.0,
            'RS' => 4170.0,
            'SC' => 4190.0,
            'SE' => 4370.0,
            'SP' => 4310.0,
            'TO' => 4440.0,
        ];

        $rows = collect($prices)
            ->map(fn (float $price, string $state): array => [
                'state' => $state,
                'price_per_kwp' => round($price, 2),
                'module_cost_average' => round($price * 0.50, 2),
                'inverter_cost_average' => round($price * 0.20, 2),
                'installation_cost_average' => round($price * 0.18, 2),
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->values()
            ->all();

        DB::connection('solar_mysql')
            ->table('solar_market_defaults')
            ->insert($rows);
    }

    public function down(): void
    {
        Schema::connection('solar_mysql')->dropIfExists('solar_market_defaults');
    }
};
