<?php

namespace App\Modules\Solar\Models;

use Illuminate\Database\Eloquent\Model;

class SolarMarketDefault extends Model
{
    protected $connection = 'solar_mysql';

    protected $table = 'solar_market_defaults';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'state',
        'price_per_kwp',
        'module_cost_average',
        'inverter_cost_average',
        'installation_cost_average',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_per_kwp' => 'decimal:2',
            'module_cost_average' => 'decimal:2',
            'inverter_cost_average' => 'decimal:2',
            'installation_cost_average' => 'decimal:2',
        ];
    }
}
