<?php

namespace App\Modules\Solar\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class SolarProject extends Model
{
    protected $connection = 'solar_mysql';

    protected $table = 'solar_projects';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'solar_customer_id',
        'name',
        'zip_code',
        'street',
        'number',
        'complement',
        'district',
        'city',
        'state',
        'latitude',
        'longitude',
        'geocoding_status',
        'monthly_consumption_kwh',
        'energy_bill_value',
        'connection_type',
        'system_power_kwp',
        'module_power',
        'module_quantity',
        'inverter_model',
        'estimated_generation_kwh',
        'solar_factor_used',
        'solar_factor_source',
        'solar_factor_fetched_at',
        'radiation_status',
        'suggested_price',
        'pricing_notes',
        'property_type',
        'utility_company',
        'energy_utility_id',
        'status',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'monthly_consumption_kwh' => 'decimal:2',
            'energy_bill_value' => 'decimal:2',
            'system_power_kwp' => 'decimal:2',
            'estimated_generation_kwh' => 'decimal:2',
            'solar_factor_used' => 'decimal:2',
            'suggested_price' => 'decimal:2',
            'module_power' => 'integer',
            'module_quantity' => 'integer',
            'energy_utility_id' => 'integer',
            'solar_factor_fetched_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(SolarCustomer::class, 'solar_customer_id');
    }

    public function energyUtility(): BelongsTo
    {
        return $this->belongsTo(EnergyUtility::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(SolarQuote::class);
    }
}
