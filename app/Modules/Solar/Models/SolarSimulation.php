<?php

namespace App\Modules\Solar\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SolarSimulation extends Model
{
    protected $connection = 'solar_mysql';

    protected $table = 'solar_simulations';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'solar_project_id',
        'name',
        'system_power_kwp',
        'module_power',
        'module_quantity',
        'estimated_generation_kwh',
        'area_estimated',
        'inverter_model',
        'solar_factor_used',
        'solar_factor_source',
        'suggested_price',
        'payment_mode',
        'upfront_payment',
        'installment_count',
        'monthly_interest_rate',
        'tariff_growth_yearly',
        'estimated_module_cost',
        'estimated_inverter_cost',
        'estimated_structure_cost',
        'estimated_installation_cost',
        'estimated_kit_cost',
        'estimated_gross_profit',
        'estimated_monthly_savings',
        'estimated_annual_savings',
        'estimated_lifetime_savings',
        'estimated_roi',
        'estimated_payback_months',
        'estimated_financed_amount',
        'estimated_installment_value',
        'estimated_net_monthly_benefit',
        'estimated_five_year_savings',
        'system_composition_json',
        'status',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'system_power_kwp' => 'decimal:2',
            'module_power' => 'integer',
            'module_quantity' => 'integer',
            'estimated_generation_kwh' => 'decimal:2',
            'area_estimated' => 'decimal:2',
            'solar_factor_used' => 'decimal:2',
            'suggested_price' => 'decimal:2',
            'upfront_payment' => 'decimal:2',
            'installment_count' => 'integer',
            'monthly_interest_rate' => 'decimal:3',
            'tariff_growth_yearly' => 'decimal:2',
            'estimated_module_cost' => 'decimal:2',
            'estimated_inverter_cost' => 'decimal:2',
            'estimated_structure_cost' => 'decimal:2',
            'estimated_installation_cost' => 'decimal:2',
            'estimated_kit_cost' => 'decimal:2',
            'estimated_gross_profit' => 'decimal:2',
            'estimated_monthly_savings' => 'decimal:2',
            'estimated_annual_savings' => 'decimal:2',
            'estimated_lifetime_savings' => 'decimal:2',
            'estimated_roi' => 'decimal:2',
            'estimated_payback_months' => 'integer',
            'estimated_financed_amount' => 'decimal:2',
            'estimated_installment_value' => 'decimal:2',
            'estimated_net_monthly_benefit' => 'decimal:2',
            'estimated_five_year_savings' => 'decimal:2',
            'system_composition_json' => 'array',
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

    public function quotes(): HasMany
    {
        return $this->hasMany(SolarQuote::class, 'solar_simulation_id');
    }
}
