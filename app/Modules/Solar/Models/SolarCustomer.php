<?php

namespace App\Modules\Solar\Models;

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
        'document',
        'email',
        'phone',
    ];
}
