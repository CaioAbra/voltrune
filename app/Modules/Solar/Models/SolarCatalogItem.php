<?php

namespace App\Modules\Solar\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SolarCatalogItem extends Model
{
    protected $connection = 'solar_mysql';

    protected $table = 'solar_catalog_items';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'type',
        'category',
        'name',
        'brand',
        'sku',
        'supplier',
        'unit_label',
        'default_quantity',
        'default_cost',
        'default_price',
        'is_active',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'default_quantity' => 'decimal:2',
            'default_cost' => 'decimal:2',
            'default_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function quoteItems(): HasMany
    {
        return $this->hasMany(SolarQuoteItem::class, 'solar_catalog_item_id');
    }
}
