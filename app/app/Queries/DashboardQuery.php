<?php

namespace App\Queries;

use App\Models\CampaignTest;
use App\Models\OptimizationFeedback;
use App\Models\ProductProject;
use App\Models\ProjectActivity;
use App\Models\User;

class DashboardQuery
{
    public function for(User $user): array
    {
        $isAdministrator = $user->hasRole('administrator');
        $departmentCode = $user->department?->code;

        $projects = ProductProject::query()
            ->with(['owner', 'ownerDepartment'])
            ->when(! $isAdministrator, fn ($query) => $query->where('current_stage', $departmentCode))
            ->latest()
            ->get();

        $feedback = OptimizationFeedback::query()
            ->with('project')
            ->where('status', '!=', 'resolved')
            ->when(! $isAdministrator, fn ($query) => $query->where('target_stage', $departmentCode))
            ->latest()
            ->get();

        $metrics = CampaignTest::query()
            ->whereIn('product_project_id', $projects->pluck('id'))
            ->selectRaw('COALESCE(SUM(spend), 0) as spend, COALESCE(SUM(impressions), 0) as impressions, COALESCE(SUM(clicks), 0) as clicks, COALESCE(SUM(conversions), 0) as conversions')
            ->first();

        $metrics->ctr = $metrics->impressions > 0
            ? round(($metrics->clicks / $metrics->impressions) * 100, 2)
            : 0;

        $activities = ProjectActivity::query()
            ->with(['project', 'actor'])
            ->when(! $isAdministrator, fn ($query) => $query->whereIn('product_project_id', $projects->pluck('id')))
            ->latest()
            ->limit(8)
            ->get();

        $collaborationOverview = ProductProject::query()
            ->where('status', '!=', 'archived')
            ->withCount(['researchSources', 'skus', 'sources', 'landingPages', 'creativeAssets', 'campaignTests'])
            ->get();

        return compact('projects', 'feedback', 'metrics', 'activities', 'isAdministrator', 'collaborationOverview');
    }
}
