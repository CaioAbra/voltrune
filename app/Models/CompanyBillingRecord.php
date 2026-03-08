<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyBillingRecord extends Model
{
    use HasFactory;

    protected $connection = 'hub_mysql';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'financial_status',
        'payment_method',
        'last_payment_date',
        'paid_amount',
        'next_billing_date',
        'financial_notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_payment_date' => 'date',
            'next_billing_date' => 'date',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
