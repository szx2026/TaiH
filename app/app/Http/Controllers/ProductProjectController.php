<?php

namespace App\Http\Controllers;

use App\Actions\Projects\CreateProductProject;
use App\Http\Requests\StoreProductProjectRequest;
use App\Models\ProductProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductProjectController extends Controller
{
    public function index(): View
    {
        return view('projects.index', [
            'projects' => ProductProject::query()->latest()->get(),
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
