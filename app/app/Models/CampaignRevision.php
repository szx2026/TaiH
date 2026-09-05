<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignRevision extends Model
{
    protected $fillable = ['campaign_test_id', 'metrics', 'conclusion', 'adjustment_items', 'created_by'];
    protected function casts(): array { return ['metrics' => 'array']; }
    public function campaign(): BelongsTo { return $this->belongsTo(CampaignTest::class, 'campaign_test_id'); }
}
