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
    public function update(Request $request, ProductProject $project, CampaignTest $campaign): RedirectResponse
    {
        abort_unless(
            ($request->user()?->department?->code === 'traffic_growth' || $request->user()?->hasRole('administrator'))
                && $campaign->product_project_id === $project->id,
            403,
        );
        $data = $request->validate([
            'spend' => ['required', 'numeric', 'min:0'], 'cost_per_click' => ['required', 'numeric', 'min:0'],
            'add_to_cart_conversions' => ['required', 'integer', 'min:0'], 'checkout_conversions' => ['required', 'integer', 'min:0'],
            'conclusion' => ['required', 'string', 'max:4000'], 'adjustment_items' => ['required', 'string', 'max:4000'],
        ]);
        DB::transaction(function () use ($campaign, $data, $request): void {
            $campaign->update(collect($data)->only(['spend', 'cost_per_click', 'add_to_cart_conversions', 'checkout_conversions'])->all());
            $campaign->revisions()->create(['metrics' => collect($campaign->only(['spend', 'cost_per_click', 'add_to_cart_conversions', 'checkout_conversions']))->all(), 'conclusion' => $data['conclusion'], 'adjustment_items' => $data['adjustment_items'], 'created_by' => $request->user()->id]);
        });
        return to_route('projects.index', ['stage' => 'traffic_growth', 'project' => $project]);
    }
    public function store(Request $request, ProductProject $project): RedirectResponse
    {
        abort_unless($request->user()?->department?->code === 'traffic_growth' || $request->user()?->hasRole('administrator'), 403);

        $data = $request->validate([
            'platform' => ['required', Rule::in(['facebook'])],
            'campaign_name' => ['required', 'string', 'max:255'],
            'spend' => ['required', 'numeric', 'min:0'],
            'cost_per_click' => ['nullable', 'numeric', 'min:0'],
            'add_to_cart_conversions' => ['nullable', 'integer', 'min:0'],
            'checkout_conversions' => ['nullable', 'integer', 'min:0'],
            'impressions' => ['nullable', 'integer', 'min:0'],
            'clicks' => ['nullable', 'integer', 'min:0'],
            'conversions' => ['nullable', 'integer', 'min:0'],
            'detail_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
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
            $detailImagePath = $request->file('detail_image')?->store('campaign-details/'.$project->id, 'local');
            $impressions = (int) ($data['impressions'] ?? 0);
            $clicks = (int) ($data['clicks'] ?? 0);
            $checkoutConversions = (int) ($data['checkout_conversions'] ?? $data['conversions'] ?? 0);
            $ctr = $impressions === 0 ? 0 : round(($clicks / $impressions) * 100, 2);
            $campaign = CampaignTest::create([
                'product_project_id' => $project->id,
                'platform' => $data['platform'],
                'campaign_name' => $data['campaign_name'],
                'spend' => $data['spend'],
                // Legacy columns stay populated for historical compatibility.
                'impressions' => $impressions,
                'clicks' => $clicks,
                'conversions' => $checkoutConversions,
                'cost_per_click' => $data['cost_per_click'] ?? ($clicks > 0 ? round($data['spend'] / $clicks, 2) : null),
                'add_to_cart_conversions' => $data['add_to_cart_conversions'] ?? null,
                'checkout_conversions' => $checkoutConversions,
                'detail_image_path' => $detailImagePath,
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
                'cost_per_click' => $campaign->cost_per_click,
                'add_to_cart_conversions' => $campaign->add_to_cart_conversions,
                'checkout_conversions' => $campaign->checkout_conversions,
                'creative_asset_id' => $campaign->creative_asset_id,
                'creative_asset_title' => $campaign->creativeAsset->title,
                'landing_page_id' => $campaign->landing_page_id,
                'landing_page_title' => $campaign->landingPage->title,
            ]);
        });

        return to_route('projects.index', ['stage' => 'traffic_growth', 'project' => $project]);
    }
}
