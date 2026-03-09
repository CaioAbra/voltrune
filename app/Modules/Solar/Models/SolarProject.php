<?php

namespace App\Modules\Solar\Models;

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
        'status',
    ];
}
