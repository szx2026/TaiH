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
        $nextStages = [
            'market_research' => 'website_operations',
            'website_operations' => 'content_creative',
            'content_creative' => 'traffic_growth',
        ];

        $data = $request->validate([
            'target_stage' => ['required', Rule::in(array_values($nextStages))],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        if (($nextStages[$project->current_stage] ?? null) !== $data['target_stage'] || $request->user()?->department?->code !== $project->current_stage) {
            abort(403);
        }

        if ($project->current_stage === 'market_research' && $project->researchSources()->doesntExist()) {
            return back()->withErrors([
                'research_sources' => '提交前至少需要一条选品调研证据。',
            ]);
        }

        if ($project->current_stage === 'website_operations' && ($project->sources()->doesntExist() || $project->skus()->doesntExist() || $project->landingPages()->doesntExist())) {
            return back()->withErrors(['handoff' => '交接给创意部前，请完成 1688 货源、SKU 与至少一个落地页版本。']);
        }

        if ($project->current_stage === 'content_creative' && $project->creativeAssets()->doesntExist()) {
            return back()->withErrors(['handoff' => '交接给流量部前，至少需要一条可投放素材。']);
        }

        $submitProjectStage->handle($project, $request->user(), $data['target_stage'], $data['note'] ?? null);

        return to_route('projects.index');
    }
}
