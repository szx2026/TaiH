<?php

namespace App\Http\Controllers;

use App\Models\OptimizationFeedback;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackCenterController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $feedback = OptimizationFeedback::query()->with('project')
            ->where('status', '!=', 'resolved')
            ->when(! $user->hasRole('administrator'), fn ($query) => $query->where('target_stage', $user->department?->code))
            ->latest()->get();

        return view('feedback.index', compact('feedback'));
    }
}
