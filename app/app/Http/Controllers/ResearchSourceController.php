<?php

namespace App\Http\Controllers;

use App\Actions\Activity\RecordProjectActivity;
use App\Models\ProductProject;
use App\Models\ResearchSource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ResearchSourceController extends Controller
{
    public function store(Request $request, ProductProject $project): RedirectResponse
    {
        abort_unless($request->user()?->department?->code === 'market_research' || $request->user()?->hasRole('administrator'), 403);

        $data = $request->validate([
            'platform' => ['required', Rule::in(['tiktok', 'facebook_ads', 'amazon', 'independent_store'])],
            'custom_source_name' => ['nullable', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:2048'],
            'evidence_note' => ['nullable', 'string', 'max:4000'],
        ]);

        $source = ResearchSource::create([
            'product_project_id' => $project->id,
            ...$data,
            'captured_at' => now(),
            'created_by' => $request->user()->id,
        ]);

        app(RecordProjectActivity::class)->handle($project, $request->user(), 'research_source.created', [
            'research_source_id' => $source->id,
            'platform' => $source->platform,
            'url' => $source->url,
        ]);

        return to_route('projects.index', ['stage' => 'market_research', 'project' => $project]);
    }
}
