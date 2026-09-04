<?php

namespace App\Http\Controllers;

use App\Actions\Projects\SubmitProjectStage;
use App\Models\ProductProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectWorkflowController extends Controller
{
    public function submit(Request $request, ProductProject $project, SubmitProjectStage $submitProjectStage): RedirectResponse
    {
        $data = $request->validate([
            'target_stage' => ['required', Rule::in(['website_operations'])],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($project->current_stage !== 'market_research' || $request->user()?->department?->code !== 'market_research') {
            abort(403);
        }

        if ($project->researchSources()->doesntExist()) {
            return back()->withErrors([
                'research_sources' => '提交前至少需要一条选品调研证据。',
            ]);
        }

        $submitProjectStage->handle($project, $request->user(), $data['target_stage'], $data['note'] ?? null);

        return to_route('projects.index');
    }
}
