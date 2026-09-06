<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreativeAsset extends Model
{
    protected $fillable = [
        'product_project_id', 'landing_page_id', 'title', 'asset_type', 'asset_types', 'source_type',
        'external_url', 'reference_urls', 'storage_disk', 'storage_path', 'copy_text', 'notes', 'status', 'created_by',
    ];

    protected function casts(): array
    {
        return ['asset_types' => 'array', 'reference_urls' => 'array'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProductProject::class, 'product_project_id');
    }

    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(LandingPage::class);
    }
}
