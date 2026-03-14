<?php

namespace App\Modules\Solar\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SolarCompanySetting extends Model
{
    public const MARGIN_MODE_FIXED = 'fixed';
    public const MARGIN_MODE_RANGE = 'range';

    protected $connection = 'solar_mysql';

    protected $table = 'solar_company_settings';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'default_module_power',
        'price_per_kwp',
        'margin_percent',
        'margin_mode',
        'default_inverter_model',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'company_id' => 'integer',
            'default_module_power' => 'integer',
            'price_per_kwp' => 'decimal:2',
            'margin_percent' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function marginRanges(): HasMany
    {
        return $this->hasMany(SolarCompanyMarginRange::class, 'solar_company_setting_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
