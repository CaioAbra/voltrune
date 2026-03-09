<?php

namespace App\Modules\Solar\Models;

use Illuminate\Database\Eloquent\Model;

class SolarQuoteItem extends Model
{
    protected $connection = 'solar_mysql';

    protected $table = 'solar_quote_items';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'solar_quote_id',
        'description',
        'quantity',
        'unit_price',
        'total_price',
    ];
}
