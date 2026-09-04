<?php

namespace App\Http\Controllers;

use App\Models\CampaignTest;
use App\Models\OptimizationFeedback;
use App\Models\ProductProject;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdministrator = $user->hasRole('administrator');
        $departmentCode = $user->department?->code;

        $projects = ProductProject::query()
            ->when(! $isAdministrator, fn ($query) => $query->where('current_stage', $departmentCode))
            ->latest()
            ->get();

        $feedback = OptimizationFeedback::query()
            ->with('project')
            ->where('status', '!=', 'resolved')
            ->when(! $isAdministrator, fn ($query) => $query->where('target_stage', $departmentCode))
            ->latest()
            ->get();

        $projectIds = $projects->pluck('id');
        $metrics = CampaignTest::query()
            ->whereIn('product_project_id', $projectIds)
            ->selectRaw('COALESCE(SUM(spend), 0) as spend, COALESCE(SUM(impressions), 0) as impressions, COALESCE(SUM(clicks), 0) as clicks, COALESCE(SUM(conversions), 0) as conversions')
            ->first();

        return view('dashboard.index', compact('projects', 'feedback', 'metrics', 'isAdministrator'));
    }
}
