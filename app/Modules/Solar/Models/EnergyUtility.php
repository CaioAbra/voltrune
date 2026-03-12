<?php

namespace App\Modules\Solar\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EnergyUtility extends Model
{
    protected $connection = 'solar_mysql';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'state',
        'cities_json',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cities_json' => 'array',
        ];
    }

    public function projects(): HasMany
    {
        return $this->hasMany(SolarProject::class);
    }
}
