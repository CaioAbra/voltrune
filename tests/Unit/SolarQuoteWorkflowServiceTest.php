<?php

namespace Tests\Unit;

use App\Modules\Solar\Models\SolarQuote;
use App\Modules\Solar\Services\SolarQuoteWorkflowService;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SolarQuoteWorkflowServiceTest extends TestCase
{
    public function test_it_initializes_first_version_for_a_new_quote(): void
    {
        $service = new SolarQuoteWorkflowService();
        $versioning = $service->initializeVersioning(9, null, Carbon::create(2026, 3, 15, 10, 30, 0));

        $this->assertSame(1, $versioning['version_number']);
        $this->assertNull($versioning['source_quote_id']);
        $this->assertMatchesRegularExpression('/^SOL-9-20260315-[A-Z0-9]{4}-V01$/', $versioning['proposal_code']);
        $this->assertMatchesRegularExpression('/^SOL-9-20260315-[A-Z0-9]{4}$/', $versioning['version_group_code']);
    }

    public function test_it_increments_versioning_from_the_source_quote(): void
    {
        $service = new SolarQuoteWorkflowService();
        $sourceQuote = new SolarQuote([
            'version_group_code' => 'SOL-4-20260315-ABCD',
            'version_number' => 2,
        ]);
        $sourceQuote->id = 44;

        $versioning = $service->initializeVersioning(4, $sourceQuote, Carbon::create(2026, 3, 15, 11, 0, 0));

        $this->assertSame(3, $versioning['version_number']);
        $this->assertSame(44, $versioning['source_quote_id']);
        $this->assertSame('SOL-4-20260315-ABCD', $versioning['version_group_code']);
        $this->assertSame('SOL-4-20260315-ABCD-V03', $versioning['proposal_code']);
    }

    public function test_it_marks_only_missing_status_timestamps(): void
    {
        $service = new SolarQuoteWorkflowService();
        $quote = new SolarQuote([
            'sent_at' => Carbon::create(2026, 3, 15, 9, 0, 0),
            'approved_at' => null,
            'won_at' => null,
            'lost_at' => null,
        ]);
        $timestamp = Carbon::create(2026, 3, 15, 14, 45, 0);

        $updates = $service->syncStatusTimeline($quote, 'approved', $timestamp);

        $this->assertSame('2026-03-15 09:00:00', $updates['sent_at']?->format('Y-m-d H:i:s'));
        $this->assertSame('2026-03-15 14:45:00', $updates['approved_at']?->format('Y-m-d H:i:s'));
        $this->assertNull($updates['won_at']);
        $this->assertNull($updates['lost_at']);
    }
}
