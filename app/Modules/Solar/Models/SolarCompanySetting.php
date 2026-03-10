<?php

namespace App\Modules\Solar\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolarCompanySetting extends Model
{
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
}
