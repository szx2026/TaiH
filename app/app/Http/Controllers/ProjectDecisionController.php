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
            'title' => ['required', 'string', 'max:255'],
            'details' => ['nullable', 'string', 'max:4000'],
        ]);

        $decision = ProjectDecision::create([
            'product_project_id' => $project->id,
            'decision_type' => $data['decision_type'],
            'title' => $data['title'],
            'status' => 'open',
            'details' => ['note' => $data['details'] ?? null],
            'created_by' => $request->user()->id,
        ]);

        app(RecordProjectActivity::class)->handle($project, $request->user(), 'decision.created', [
            'decision_id' => $decision->id,
            'decision_type' => $decision->decision_type,
            'title' => $decision->title,
        ]);

        return to_route('projects.workspace', ['project' => $project, 'tab' => 'operations']);
    }
}
