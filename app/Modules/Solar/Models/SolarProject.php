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
        'property_type',
        'utility_company',
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

    public function quotes(): HasMany
    {
        return $this->hasMany(SolarQuote::class);
    }
}
