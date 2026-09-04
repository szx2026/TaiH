<?php

namespace App\Http\Controllers;

use App\Actions\Activity\RecordProjectActivity;
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
            'creative_asset_id' => [
                'required',
                Rule::exists('creative_assets', 'id')->where(fn ($query) => $query
                    ->where('product_project_id', $project->id)
                    ->where('asset_type', 'video')),
            ],
            'landing_page_id' => [
                'required',
                Rule::exists('landing_pages', 'id')->where('product_project_id', $project->id),
            ],
            'feedback_target_stages' => ['nullable', 'array'],
            'feedback_target_stages.*' => ['nullable', 'distinct', Rule::in(['market_research', 'website_operations', 'content_creative'])],
            'feedback_note' => ['nullable', 'string'],
        ]);

        if (! empty($data['feedback_target_stages']) && empty($data['feedback_note'])) {
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
                'creative_asset_id' => $data['creative_asset_id'],
                'landing_page_id' => $data['landing_page_id'],
                'created_by' => $request->user()->id,
            ]);

            foreach ($data['feedback_target_stages'] ?? [] as $targetStage) {
                $feedback = OptimizationFeedback::create([
                    'product_project_id' => $project->id,
                    'campaign_test_id' => $campaign->id,
                    'target_stage' => $targetStage,
                    'note' => $data['feedback_note'],
                    'status' => 'open',
                    'created_by' => $request->user()->id,
                ]);
                app(RecordProjectActivity::class)->handle($project, $request->user(), 'feedback.created', [
                    'feedback_id' => $feedback->id,
                    'campaign_test_id' => $campaign->id,
                    'target_stage' => $feedback->target_stage,
                ]);
            }

            $campaign->load(['creativeAsset', 'landingPage']);
            app(RecordProjectActivity::class)->handle($project, $request->user(), 'campaign_test.created', [
                'campaign_test_id' => $campaign->id,
                'campaign_name' => $campaign->campaign_name,
                'ctr' => $campaign->ctr,
                'creative_asset_id' => $campaign->creative_asset_id,
                'creative_asset_title' => $campaign->creativeAsset->title,
                'landing_page_id' => $campaign->landing_page_id,
                'landing_page_title' => $campaign->landingPage->title,
            ]);
        });

        return to_route('projects.workspace', ['project' => $project, 'tab' => 'campaigns']);
    }
}
