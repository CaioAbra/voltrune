<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyProductAccess extends Model
{
    use HasFactory;

    protected $connection = 'hub_mysql';
    protected $table = 'company_product_access';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'product_key',
        'access_status',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
