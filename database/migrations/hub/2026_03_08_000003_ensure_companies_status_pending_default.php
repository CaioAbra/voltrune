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
        $schema = Schema::connection('hub_mysql');

        if (! $schema->hasTable('companies')) {
            return;
        }

        if (! $schema->hasColumn('companies', 'status')) {
            $schema->table('companies', function (Blueprint $table) {
                $table->string('status')->default('pending')->after('slug');
            });

            return;
        }

        DB::connection('hub_mysql')->statement("ALTER TABLE companies MODIFY status VARCHAR(255) NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $schema = Schema::connection('hub_mysql');

        if (! $schema->hasTable('companies') || ! $schema->hasColumn('companies', 'status')) {
            return;
        }

        DB::connection('hub_mysql')->statement("ALTER TABLE companies MODIFY status VARCHAR(255) NOT NULL DEFAULT 'active'");
    }
};
