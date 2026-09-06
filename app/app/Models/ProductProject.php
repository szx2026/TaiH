<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_code',
        'product_name',
        'category',
        'market',
        'priority',
        'current_stage',
        'status',
        'owner_department_id',
        'owner_user_id',
        'created_by',
        'outcome', 'outcome_reason', 'next_action', 'outcome_recorded_at', 'outcome_recorded_by',
    ];

    public function ownerDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'owner_department_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function researchSources(): HasMany
    {
        return $this->hasMany(ResearchSource::class);
    }

    public function workflowTransitions(): HasMany
    {
        return $this->hasMany(WorkflowTransition::class);
    }

    public function sources(): HasMany
    {
        return $this->hasMany(ProductSource::class);
    }

    public function skus(): HasMany
    {
        return $this->hasMany(ProductSku::class);
    }

    public function landingPages(): HasMany
    {
        return $this->hasMany(LandingPage::class);
    }

    public function creativeAssets(): HasMany
    {
        return $this->hasMany(CreativeAsset::class);
    }

    public function campaignTests(): HasMany
    {
        return $this->hasMany(CampaignTest::class);
    }

    public function optimizationFeedback(): HasMany
    {
        return $this->hasMany(OptimizationFeedback::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ProjectActivity::class)->latest();
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(ProjectDecision::class)->latest();
    }
}
