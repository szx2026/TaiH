<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OptimizationFeedback extends Model
{
    protected $table = 'optimization_feedback';

    protected $fillable = ['product_project_id', 'campaign_test_id', 'target_stage', 'note', 'response_note', 'status', 'resolved_by', 'resolved_at', 'created_by'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProductProject::class, 'product_project_id');
    }

    public function campaignTest(): BelongsTo
    {
        return $this->belongsTo(CampaignTest::class);
    }
}
