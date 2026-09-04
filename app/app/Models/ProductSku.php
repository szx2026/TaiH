<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSku extends Model
{
    protected $fillable = [
        'product_project_id',
        'product_source_id',
        'sku_code',
        'variant_name',
        'sku_status',
        'created_by',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProductProject::class, 'product_project_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(ProductSource::class, 'product_source_id');
    }
}
