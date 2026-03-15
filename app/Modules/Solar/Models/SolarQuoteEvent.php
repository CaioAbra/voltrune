<?php

namespace App\Modules\Solar\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolarQuoteEvent extends Model
{
    protected $connection = 'solar_mysql';

    protected $table = 'solar_quote_events';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'solar_quote_id',
        'event_type',
        'title',
        'description',
        'payload_json',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload_json' => 'array',
        ];
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(SolarQuote::class, 'solar_quote_id');
    }
}
