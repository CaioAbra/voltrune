<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('solar_mysql')->table('solar_quotes', function (Blueprint $table): void {
            $table->string('owner_name')->nullable()->after('title');
            $table->timestamp('next_contact_at')->nullable()->after('lost_at');
            $table->timestamp('closing_forecast_at')->nullable()->after('next_contact_at');
            $table->string('deal_temperature')->default('warm')->after('closing_forecast_at')->index();
        });
    }

    public function down(): void
    {
        Schema::connection('solar_mysql')->table('solar_quotes', function (Blueprint $table): void {
            $table->dropColumn([
                'owner_name',
                'next_contact_at',
                'closing_forecast_at',
                'deal_temperature',
            ]);
        });
    }
};
