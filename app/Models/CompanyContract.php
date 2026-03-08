<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyContract extends Model
{
    use HasFactory;

    protected $connection = 'hub_mysql';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'product_key',
        'plan_name',
        'billing_cycle',
        'negotiated_value',
        'commercial_notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'negotiated_value' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
