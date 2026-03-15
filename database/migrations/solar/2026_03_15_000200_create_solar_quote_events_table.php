<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('solar_mysql')->create('solar_quote_events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->foreignId('solar_quote_id')
                ->constrained('solar_quotes')
                ->cascadeOnDelete();
            $table->string('event_type')->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('payload_json')->nullable();
            $table->timestamps();
        });

        $connection = DB::connection('solar_mysql');
        $quotes = $connection->table('solar_quotes')
            ->select(['id', 'company_id', 'proposal_code', 'created_at', 'updated_at'])
            ->orderBy('id')
            ->get();

        foreach ($quotes as $quote) {
            $connection->table('solar_quote_events')->insert([
                'company_id' => $quote->company_id,
                'solar_quote_id' => $quote->id,
                'event_type' => 'legacy_imported',
                'title' => 'Historico inicial registrado',
                'description' => 'Esta proposta ja existia antes do fluxo de versoes e historico comercial.',
                'payload_json' => json_encode([
                    'proposal_code' => $quote->proposal_code,
                ], JSON_THROW_ON_ERROR),
                'created_at' => $quote->created_at,
                'updated_at' => $quote->updated_at,
            ]);
        }
    }

    public function down(): void
    {
        Schema::connection('solar_mysql')->dropIfExists('solar_quote_events');
    }
};
