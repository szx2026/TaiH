<?php

namespace App\Http\Controllers;

use App\Models\OptimizationFeedback;
use App\Models\ProjectDecision;
use App\Models\ProductProject;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackCenterController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdministrator = $user->hasRole('administrator');
        $stage = $isAdministrator ? null : $user->department?->code;

        $feedback = OptimizationFeedback::query()->with('project')
            ->where('status', '!=', 'resolved')
            ->when(! $isAdministrator, fn ($query) => $query->where('target_stage', $stage))
            ->latest()
            ->get()
            ->map(fn (OptimizationFeedback $item) => [
                'project' => $item->project,
                'title' => '投放反馈待处理',
                'description' => $item->note,
                'type' => '投放反馈',
                'stage' => $item->target_stage,
                'created_at' => $item->created_at,
            ]);

        $decisions = ProjectDecision::query()->with('project')
            ->where('status', 'open')
            ->when(! $isAdministrator, fn ($query) => $query->where('requested_from_stage', $stage))
            ->latest()
            ->get()
            ->map(fn (ProjectDecision $item) => [
                'project' => $item->project,
                'title' => $item->title,
                'description' => data_get($item->details, 'note') ?: '请查看并处理该项目协作请求。',
                'type' => '协作请求',
                'stage' => $item->requested_from_stage,
                'created_at' => $item->created_at,
            ]);

        $unfulfilledSpecifications = collect();
        if ($isAdministrator || $stage === 'market_research') {
            $unfulfilledSpecifications = ProjectDecision::query()
                ->with(['project.skus'])
                ->where('decision_type', 'specification')
                ->where('requested_from_stage', 'website_operations')
                ->where('status', 'resolved')
                ->latest()
                ->get()
                ->flatMap(function (ProjectDecision $decision) {
                    $withdrawn = data_get($decision->details, 'withdrawn_requested_specifications', []);

                    return collect(data_get($decision->details, 'requested_specifications', []))
                        ->map(fn ($specification) => trim((string) $specification))
                        ->filter()
                        ->reject(fn ($specification) => in_array($specification, $withdrawn, true))
                        ->reject(fn ($specification) => $decision->project->skus->contains('variant_name', $specification))
                        ->map(fn ($specification) => [
                            'project' => $decision->project,
                            'title' => '待录入产品规格：'.$specification,
                            'description' => '运营部已提出新增规格请求，等待产品部填写内部 SKU。',
                            'type' => '规格请求',
                            'stage' => 'market_research',
                            'created_at' => $decision->updated_at,
                        ]);
                });
        }

        return view('feedback.index', [
            'items' => $feedback->concat($decisions)->concat($unfulfilledSpecifications)
                ->filter(fn (array $item) => $item['project'] instanceof ProductProject)
                ->sortByDesc('created_at')
                ->values(),
            'stage' => $stage ?? 'market_research',
        ]);
    }
}
