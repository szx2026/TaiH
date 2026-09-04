<?php

namespace App\Http\Controllers;

use App\Models\CampaignTest;
use App\Models\OptimizationFeedback;
use App\Models\ProductProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CampaignTestController extends Controller
{
    public function store(Request $request, ProductProject $project): RedirectResponse
    {
        abort_unless($request->user()?->department?->code === 'traffic_growth', 403);

        $data = $request->validate([
            'platform' => ['required', Rule::in(['facebook', 'tiktok', 'other'])],
            'campaign_name' => ['required', 'string', 'max:255'],
            'spend' => ['required', 'numeric', 'min:0'],
            'impressions' => ['required', 'integer', 'min:0'],
            'clicks' => ['required', 'integer', 'min:0'],
            'conversions' => ['required', 'integer', 'min:0'],
            'feedback_target_stage' => ['nullable', Rule::in(['market_research', 'website_operations', 'content_creative'])],
            'feedback_note' => ['nullable', 'string'],
        ]);

        if (! empty($data['feedback_target_stage']) && empty($data['feedback_note'])) {
            return back()->withErrors(['feedback_note' => '填写反馈部门时，请同时填写优化建议。']);
        }

        DB::transaction(function () use ($data, $project, $request): void {
            $ctr = $data['impressions'] === 0 ? 0 : round(($data['clicks'] / $data['impressions']) * 100, 2);
            $campaign = CampaignTest::create([
                'product_project_id' => $project->id,
                'platform' => $data['platform'],
                'campaign_name' => $data['campaign_name'],
                'spend' => $data['spend'],
                'impressions' => $data['impressions'],
                'clicks' => $data['clicks'],
                'conversions' => $data['conversions'],
                'ctr' => $ctr,
                'created_by' => $request->user()->id,
            ]);

            if (! empty($data['feedback_target_stage'])) {
                OptimizationFeedback::create([
                    'product_project_id' => $project->id,
                    'campaign_test_id' => $campaign->id,
                    'target_stage' => $data['feedback_target_stage'],
                    'note' => $data['feedback_note'],
                    'status' => 'open',
                    'created_by' => $request->user()->id,
                ]);
            }
        });

        return to_route('projects.index');
    }
}
