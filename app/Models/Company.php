<?php

namespace App\Models;

use App\Modules\Solar\Models\SolarCompanySetting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    use HasFactory;

    public const PRODUCT_KEYS = [
        'solar',
        'vigilante',
        'agro',
    ];

    protected $connection = 'hub_mysql';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'status',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['role', 'is_owner'])
            ->withTimestamps();
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(CompanyContract::class);
    }

    public function productAccesses(): HasMany
    {
        return $this->hasMany(CompanyProductAccess::class);
    }

    public function billingRecords(): HasMany
    {
        return $this->hasMany(CompanyBillingRecord::class)->latest();
    }

    public function latestBillingRecord(): HasOne
    {
        return $this->hasOne(CompanyBillingRecord::class)->latestOfMany('id');
    }

    public function solarSetting(): HasOne
    {
        return $this->hasOne(SolarCompanySetting::class);
    }
}
