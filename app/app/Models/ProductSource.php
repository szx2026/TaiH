<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductSource extends Model
{
    protected $fillable = [
        'product_project_id',
        'supplier_url',
        'purchase_price',
        'currency',
        'weight_g',
        'supplier_name',
        'product_name',
        'notes',
        'created_by',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProductProject::class, 'product_project_id');
    }

    public function skus(): HasMany
    {
        return $this->hasMany(ProductSku::class);
    }
}
