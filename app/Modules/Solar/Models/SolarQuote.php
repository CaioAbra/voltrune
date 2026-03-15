<?php

namespace App\Modules\Solar\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SolarQuote extends Model
{
    protected $connection = 'solar_mysql';

    protected $table = 'solar_quotes';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'solar_project_id',
        'solar_simulation_id',
        'simulation_snapshot_json',
        'proposal_code',
        'version_group_code',
        'version_number',
        'source_quote_id',
        'title',
        'owner_name',
        'final_price',
        'total_value',
        'estimated_savings',
        'payback_months',
        'status',
        'notes',
        'sent_at',
        'approved_at',
        'won_at',
        'lost_at',
        'next_contact_at',
        'closing_forecast_at',
        'deal_temperature',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'simulation_snapshot_json' => 'array',
            'version_number' => 'integer',
            'final_price' => 'decimal:2',
            'total_value' => 'decimal:2',
            'estimated_savings' => 'decimal:2',
            'payback_months' => 'integer',
            'sent_at' => 'datetime',
            'approved_at' => 'datetime',
            'won_at' => 'datetime',
            'lost_at' => 'datetime',
            'next_contact_at' => 'datetime',
            'closing_forecast_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(SolarProject::class, 'solar_project_id');
    }

    public function simulation(): BelongsTo
    {
        return $this->belongsTo(SolarSimulation::class, 'solar_simulation_id');
    }

    public function sourceQuote(): BelongsTo
    {
        return $this->belongsTo(self::class, 'source_quote_id');
    }

    public function derivedVersions(): HasMany
    {
        return $this->hasMany(self::class, 'source_quote_id')->orderBy('version_number');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SolarQuoteItem::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(SolarQuoteEvent::class)->latest();
    }

    public function itemsTotalCost(): float
    {
        if ($this->relationLoaded('items')) {
            return (float) $this->items->sum(fn (SolarQuoteItem $item) => (float) ($item->total_cost ?? 0));
        }

        return (float) $this->items()->sum('total_cost');
    }

    public function itemsTotalPrice(): float
    {
        if ($this->relationLoaded('items')) {
            return (float) $this->items->sum(fn (SolarQuoteItem $item) => (float) ($item->total_price ?? 0));
        }

        return (float) $this->items()->sum('total_price');
    }

    public function itemsGrossProfit(): float
    {
        return $this->itemsTotalPrice() - $this->itemsTotalCost();
    }

    public function itemsMarginPercent(): float
    {
        $totalPrice = $this->itemsTotalPrice();

        if ($totalPrice <= 0) {
            return 0;
        }

        return ($this->itemsGrossProfit() / $totalPrice) * 100;
    }
}
