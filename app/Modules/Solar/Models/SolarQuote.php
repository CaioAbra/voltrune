<?php

namespace App\Modules\Solar\Models;

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
        'status',
        'subtotal',
        'discount_total',
        'grand_total',
    ];
}
