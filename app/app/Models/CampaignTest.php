<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampaignTest extends Model
{
    protected $fillable = [
        'product_project_id', 'creative_asset_id', 'landing_page_id', 'platform', 'campaign_name',
        'spend', 'impressions', 'clicks', 'conversions', 'cost_per_click', 'add_to_cart_conversions',
        'checkout_conversions', 'detail_image_path', 'ctr', 'status', 'created_by',
        'purchase_conversions', 'purchase_value', 'result_metric',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProductProject::class, 'product_project_id');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(OptimizationFeedback::class);
    }

    public function creativeAsset(): BelongsTo
    {
        return $this->belongsTo(CreativeAsset::class);
    }

    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(LandingPage::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(CampaignRevision::class)->latest();
    }
}
