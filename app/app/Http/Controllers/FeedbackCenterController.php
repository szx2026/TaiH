<?php

namespace App\Http\Controllers;

use App\Models\OptimizationFeedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FeedbackCenterController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        $user = $request->user();
        $feedback = OptimizationFeedback::query()->with('project')
            ->where('status', '!=', 'resolved')
            ->when(! $user->hasRole('administrator'), fn ($query) => $query->where('target_stage', $user->department?->code))
            ->latest()->get();

        $first = $feedback->first();

        return to_route('projects.index', array_filter([
            'stage' => $user->hasRole('administrator') ? 'traffic_growth' : $user->department?->code,
            'project' => $first?->product_project_id,
        ]));
    }
}
