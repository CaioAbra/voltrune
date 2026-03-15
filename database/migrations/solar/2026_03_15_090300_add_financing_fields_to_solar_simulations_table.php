<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('solar_mysql')->table('solar_simulations', function (Blueprint $table): void {
            $table->string('payment_mode')->default('cash')->after('suggested_price');
            $table->decimal('upfront_payment', 12, 2)->nullable()->after('payment_mode');
            $table->unsignedInteger('installment_count')->nullable()->after('upfront_payment');
            $table->decimal('monthly_interest_rate', 8, 3)->nullable()->after('installment_count');
            $table->decimal('tariff_growth_yearly', 8, 2)->nullable()->after('monthly_interest_rate');
            $table->decimal('estimated_financed_amount', 12, 2)->nullable()->after('estimated_payback_months');
            $table->decimal('estimated_installment_value', 12, 2)->nullable()->after('estimated_financed_amount');
            $table->decimal('estimated_net_monthly_benefit', 12, 2)->nullable()->after('estimated_installment_value');
            $table->decimal('estimated_five_year_savings', 14, 2)->nullable()->after('estimated_net_monthly_benefit');
        });
    }

    public function down(): void
    {
        Schema::connection('solar_mysql')->table('solar_simulations', function (Blueprint $table): void {
            $table->dropColumn([
                'payment_mode',
                'upfront_payment',
                'installment_count',
                'monthly_interest_rate',
                'tariff_growth_yearly',
                'estimated_financed_amount',
                'estimated_installment_value',
                'estimated_net_monthly_benefit',
                'estimated_five_year_savings',
            ]);
        });
    }
};
