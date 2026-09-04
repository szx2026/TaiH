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

        return view('projects.index', [
            'projects' => ProductProject::query()
                ->when($filters['stage'] ?? null, fn ($query, $stage) => $query->where('current_stage', $stage))
                ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
                ->when($filters['market'] ?? null, fn ($query, $market) => $query->where('market', $market))
                ->when($filters['priority'] ?? null, fn ($query, $priority) => $query->where('priority', $priority))
                ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($query) => $query->where('product_name', 'like', "%{$search}%")->orWhere('project_code', 'like', "%{$search}%")))
                ->latest()
                ->get(),
            'filters' => $filters,
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
}
