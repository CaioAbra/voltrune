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
        Schema::connection('hub_mysql')->table('company_user', function (Blueprint $table): void {
            $table->index('user_id', 'company_user_user_id_lookup_index');
        });

        Schema::connection('hub_mysql')->table('companies', function (Blueprint $table): void {
            $table->index('status', 'companies_status_lookup_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('hub_mysql')->table('companies', function (Blueprint $table): void {
            $table->dropIndex('companies_status_lookup_index');
        });

        Schema::connection('hub_mysql')->table('company_user', function (Blueprint $table): void {
            $table->dropIndex('company_user_user_id_lookup_index');
        });
    }
};
