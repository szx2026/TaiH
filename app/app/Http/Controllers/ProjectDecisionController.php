<?php

namespace App\Http\Controllers;

use App\Actions\Activity\RecordProjectActivity;
use App\Models\ProductProject;
use App\Models\ProjectDecision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectDecisionController extends Controller
{
    public function store(Request $request, ProductProject $project): RedirectResponse
    {
        abort_unless(in_array($request->user()?->department?->code, ['market_research', 'website_operations'], true) || $request->user()?->hasRole('administrator'), 403);

        $data = $request->validate([
            'decision_type' => ['required', Rule::in(['sku', 'pricing', 'specification', 'landing_page'])],
            'requested_from_stage' => ['required', Rule::in(['market_research', 'website_operations'])],
            'title' => ['required', 'string', 'max:255'],
            'details' => ['nullable', 'string', 'max:4000'],
        ]);

        $department = $request->user()?->department?->code;
        abort_unless(
            $request->user()?->hasRole('administrator')
            || ($department === 'market_research' && $data['requested_from_stage'] === 'website_operations')
            || ($department === 'website_operations' && $data['requested_from_stage'] === 'market_research'),
            403,
        );

        $decision = ProjectDecision::create([
            'product_project_id' => $project->id,
            'decision_type' => $data['decision_type'],
            'requested_from_stage' => $data['requested_from_stage'],
            'title' => $data['title'],
            'status' => 'open',
            'details' => ['note' => $data['details'] ?? null],
            'created_by' => $request->user()->id,
        ]);

        app(RecordProjectActivity::class)->handle($project, $request->user(), 'decision.created', [
            'decision_id' => $decision->id,
            'decision_type' => $decision->decision_type,
            'requested_from_stage' => $decision->requested_from_stage,
            'title' => $decision->title,
        ]);

        return to_route('projects.workspace', ['project' => $project, 'tab' => 'operations']);
    }
}
