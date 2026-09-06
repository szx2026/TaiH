<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LandingPage extends Model
{
    protected $fillable = [
        'product_project_id', 'version', 'title', 'page_url', 'detail_image_path', 'selling_price',
        'currency', 'specifications', 'status', 'created_by',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProductProject::class, 'product_project_id');
    }

    public function skus(): BelongsToMany
    {
        return $this->belongsToMany(ProductSku::class, 'landing_page_skus');
    }
}
