<?php

namespace App\Modules\Solar\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class SolarCustomer extends Model
{
    protected $connection = 'solar_mysql';

    protected $table = 'solar_customers';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'email',
        'phone',
        'document',
        'city',
        'state',
        'notes',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(SolarProject::class);
    }
}
