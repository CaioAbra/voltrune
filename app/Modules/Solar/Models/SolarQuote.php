<?php

namespace App\Modules\Solar\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

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
        'title',
        'final_price',
        'total_value',
        'estimated_savings',
        'payback_months',
        'status',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'final_price' => 'decimal:2',
            'total_value' => 'decimal:2',
            'estimated_savings' => 'decimal:2',
            'payback_months' => 'integer',
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

    public function items(): HasMany
    {
        return $this->hasMany(SolarQuoteItem::class);
    }
}
