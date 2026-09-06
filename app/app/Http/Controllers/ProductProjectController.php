<?php

namespace App\Http\Controllers;

use App\Actions\Projects\CreateProductProject;
use App\Http\Requests\FilterProductProjectsRequest;
use App\Http\Requests\StoreProductProjectRequest;
use App\Models\ProductProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductProjectController extends Controller
{
    public function index(FilterProductProjectsRequest $request): View
    {
        $filters = $request->validated();
        $departmentWorkspaces = [
            'market_research' => [
                'eyebrow' => '市场研究部',
                'title' => '市场研究部工作台',
                'description' => '完成选品证据、公司产品管理系统开品与内部 SKU 回填。',
                'list_label' => '待选品与 SKU 回填项目',
                'allows_create' => true,
            ],
            'website_operations' => [
                'eyebrow' => '网站运营部',
                'title' => '网站运营部工作台',
                'description' => '处理 1688 货源、Shopify 上架与产品页、价格和规格。',
                'list_label' => '当前待处理项目',
                'allows_create' => false,
            ],
            'content_creative' => [
                'eyebrow' => '内容创意部',
                'title' => '内容创意部工作台',
                'description' => '围绕已选产品制作、上传并完善可投放的视频素材。',
                'list_label' => '待制作素材项目',
                'allows_create' => false,
            ],
            'traffic_growth' => [
                'eyebrow' => '流量增长部',
                'title' => '流量增长部工作台',
                'description' => '选择视频与 Shopify 页面投放，并将测试数据反馈给相关部门。',
                'list_label' => '待投放与复盘项目',
                'allows_create' => false,
            ],
        ];
        $departmentWorkspace = $departmentWorkspaces[$filters['stage'] ?? ''] ?? null;

        $projects = ProductProject::query()
                ->where('status', '!=', 'archived')
                // Departments collaborate on the same active product in parallel.
                // `current_stage` remains a progress indicator, not an access filter.
                ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
                ->when($filters['market'] ?? null, fn ($query, $market) => $query->where('market', $market))
                ->when($filters['priority'] ?? null, fn ($query, $priority) => $query->where('priority', $priority))
                ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($query) => $query->where('product_name', 'like', "%{$search}%")->orWhere('project_code', 'like', "%{$search}%")))
                ->latest()
                ->get();

        $selectedProject = isset($filters['project'])
            ? ProductProject::query()->where('status', '!=', 'archived')->whereKey($filters['project'])->with(['researchSources', 'skus', 'sources', 'landingPages.skus', 'creativeAssets', 'campaignTests.revisions', 'optimizationFeedback', 'decisions'])->first()
            : $projects->first();

        return view('projects.index', [
            'projects' => $projects,
            'filters' => $filters,
            'departmentWorkspace' => $departmentWorkspace,
            'selectedProject' => $selectedProject,
        ]);
    }

    public function store(StoreProductProjectRequest $request, CreateProductProject $createProductProject): RedirectResponse
    {
        $createProductProject->handle($request->user(), $request->validated());

        return to_route('projects.index');
    }

    public function show(ProductProject $project): View
    {
        return view('projects.show', [
            'project' => $project->load(['skus', 'landingPages.skus', 'creativeAssets', 'campaignTests', 'optimizationFeedback']),
        ]);
    }

    public function recycleBin(): View
    {
        return view('projects.recycle-bin', [
            'projects' => ProductProject::query()->where('status', 'archived')->latest()->get(),
        ]);
    }

    public function archive(\Illuminate\Http\Request $request, ProductProject $project): RedirectResponse
    {
        abort_unless($request->user()?->department?->code === 'traffic_growth' || $request->user()?->hasRole('administrator'), 403);
        $project->update(['status' => 'archived']);

        return to_route('projects.index', ['stage' => 'traffic_growth', 'project' => $project]);
    }

    public function restore(\Illuminate\Http\Request $request, ProductProject $project): RedirectResponse
    {
        abort_unless($request->user()?->department?->code === 'traffic_growth' || $request->user()?->hasRole('administrator'), 403);
        $project->update(['status' => 'in_progress']);

        return to_route('projects.index', ['stage' => 'traffic_growth', 'project' => $project]);
    }

    public function recordOutcome(\Illuminate\Http\Request $request, ProductProject $project): RedirectResponse
    {
        abort_unless($request->user()?->department?->code === 'traffic_growth' || $request->user()?->hasRole('administrator'), 403);
        $data = $request->validate(['outcome' => ['required', \Illuminate\Validation\Rule::in(['scale', 'retest', 'adjust_retest', 'pause', 'reject', 'complete'])], 'outcome_reason' => ['required', 'string', 'max:4000'], 'next_action' => ['required', 'string', 'max:4000']]);
        $project->update([...$data, 'outcome_recorded_at' => now(), 'outcome_recorded_by' => $request->user()->id]);
        return to_route('projects.index', ['stage' => 'traffic_growth', 'project' => $project]);
    }
}
