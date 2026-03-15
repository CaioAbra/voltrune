<?php

namespace App\Modules\Solar\Services;

use App\Modules\Solar\Models\SolarQuote;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SolarQuoteWorkflowService
{
    /**
     * @return array{proposal_code: string, version_group_code: string, version_number: int, source_quote_id: ?int}
     */
    public function initializeVersioning(int $companyId, ?SolarQuote $sourceQuote = null, ?Carbon $timestamp = null): array
    {
        $timestamp ??= now();

        if ($sourceQuote instanceof SolarQuote) {
            $groupCode = $sourceQuote->version_group_code ?: $this->legacyGroupCode($sourceQuote);
            $versionNumber = max((int) $sourceQuote->version_number, 1) + 1;

            return [
                'proposal_code' => $this->makeProposalCode($groupCode, $versionNumber),
                'version_group_code' => $groupCode,
                'version_number' => $versionNumber,
                'source_quote_id' => $sourceQuote->id,
            ];
        }

        $groupCode = $this->makeVersionGroupCode($companyId, $timestamp);

        return [
            'proposal_code' => $this->makeProposalCode($groupCode, 1),
            'version_group_code' => $groupCode,
            'version_number' => 1,
            'source_quote_id' => null,
        ];
    }

    /**
     * @return array{sent_at: ?Carbon, approved_at: ?Carbon, won_at: ?Carbon, lost_at: ?Carbon}
     */
    public function syncStatusTimeline(SolarQuote $quote, string $status, ?Carbon $timestamp = null): array
    {
        $timestamp ??= now();
        $updates = [
            'sent_at' => $quote->sent_at,
            'approved_at' => $quote->approved_at,
            'won_at' => $quote->won_at,
            'lost_at' => $quote->lost_at,
        ];

        $column = match ($status) {
            'sent' => 'sent_at',
            'approved' => 'approved_at',
            'won' => 'won_at',
            'lost' => 'lost_at',
            default => null,
        };

        if ($column !== null && $updates[$column] === null) {
            $updates[$column] = $timestamp;
        }

        return $updates;
    }

    public function makeProposalCode(string $groupCode, int $versionNumber): string
    {
        return $groupCode . '-V' . str_pad((string) $versionNumber, 2, '0', STR_PAD_LEFT);
    }

    private function makeVersionGroupCode(int $companyId, Carbon $timestamp): string
    {
        return sprintf(
            'SOL-%d-%s-%s',
            $companyId,
            $timestamp->format('Ymd'),
            Str::upper(Str::random(4)),
        );
    }

    private function legacyGroupCode(SolarQuote $quote): string
    {
        $createdAt = $quote->created_at instanceof Carbon ? $quote->created_at : now();

        return sprintf('SOL-%s-%06d', $createdAt->format('Ymd'), (int) $quote->id);
    }
}
