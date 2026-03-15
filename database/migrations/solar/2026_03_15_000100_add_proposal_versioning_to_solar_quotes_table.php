<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('solar_mysql')->table('solar_quotes', function (Blueprint $table): void {
            $table->string('proposal_code')->nullable()->after('simulation_snapshot_json');
            $table->string('version_group_code')->nullable()->after('proposal_code');
            $table->unsignedInteger('version_number')->default(1)->after('version_group_code');
            $table->foreignId('source_quote_id')
                ->nullable()
                ->after('version_number')
                ->constrained('solar_quotes')
                ->nullOnDelete();
            $table->timestamp('sent_at')->nullable()->after('notes');
            $table->timestamp('approved_at')->nullable()->after('sent_at');
            $table->timestamp('won_at')->nullable()->after('approved_at');
            $table->timestamp('lost_at')->nullable()->after('won_at');

            $table->index('proposal_code');
            $table->index('version_group_code');
        });

        $connection = DB::connection('solar_mysql');

        $connection->table('solar_quotes')
            ->select(['id', 'status', 'created_at'])
            ->orderBy('id')
            ->chunkById(100, function ($quotes) use ($connection): void {
                foreach ($quotes as $quote) {
                    $createdAt = $quote->created_at
                        ? Carbon::parse($quote->created_at)
                        : now();
                    $groupCode = sprintf('SOL-%s-%06d', $createdAt->format('Ymd'), (int) $quote->id);
                    $timeline = [
                        'sent_at' => null,
                        'approved_at' => null,
                        'won_at' => null,
                        'lost_at' => null,
                    ];

                    switch ((string) $quote->status) {
                        case 'sent':
                            $timeline['sent_at'] = $createdAt;
                            break;
                        case 'approved':
                            $timeline['approved_at'] = $createdAt;
                            break;
                        case 'won':
                            $timeline['won_at'] = $createdAt;
                            break;
                        case 'lost':
                            $timeline['lost_at'] = $createdAt;
                            break;
                    }

                    $connection->table('solar_quotes')
                        ->where('id', $quote->id)
                        ->update([
                            'proposal_code' => $groupCode . '-V01',
                            'version_group_code' => $groupCode,
                            'version_number' => 1,
                            'sent_at' => $timeline['sent_at'],
                            'approved_at' => $timeline['approved_at'],
                            'won_at' => $timeline['won_at'],
                            'lost_at' => $timeline['lost_at'],
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::connection('solar_mysql')->table('solar_quotes', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('source_quote_id');
            $table->dropIndex(['proposal_code']);
            $table->dropIndex(['version_group_code']);
            $table->dropColumn([
                'proposal_code',
                'version_group_code',
                'version_number',
                'sent_at',
                'approved_at',
                'won_at',
                'lost_at',
            ]);
        });
    }
};
