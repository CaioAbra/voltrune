<?php

namespace App\Modules\Solar\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolarCompanyMarginRange extends Model
{
    protected $connection = 'solar_mysql';

    protected $table = 'solar_company_margin_ranges';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'solar_company_setting_id',
        'min_kwp',
        'max_kwp',
        'margin_percent',
        'requires_negotiation',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'solar_company_setting_id' => 'integer',
            'min_kwp' => 'decimal:2',
            'max_kwp' => 'decimal:2',
            'margin_percent' => 'decimal:2',
            'requires_negotiation' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function setting(): BelongsTo
    {
        return $this->belongsTo(SolarCompanySetting::class, 'solar_company_setting_id');
    }
}
