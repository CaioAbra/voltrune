<?php

namespace App\Modules\Solar\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'type',
        'category',
        'name',
        'description',
        'quantity',
        'unit_cost',
        'unit_price',
        'total_cost',
        'total_price',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => 'string',
            'category' => 'string',
            'quantity' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(SolarQuote::class, 'solar_quote_id');
    }
}
