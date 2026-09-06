<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeProductDepartment($request);
        $data = $request->validate(['name' => ['required', 'string', 'max:100', 'unique:product_categories,name']]);
        ProductCategory::create(['name' => trim($data['name']), 'created_by' => $request->user()->id]);

        return to_route('projects.index', ['stage' => 'market_research'])->with('status', '产品类目已添加。');
    }

    public function destroy(Request $request, ProductCategory $category): RedirectResponse
    {
        $this->authorizeProductDepartment($request);
        $category->delete();

        return to_route('projects.index', ['stage' => 'market_research'])->with('status', '产品类目已删除；已有项目保留原标签。');
    }

    private function authorizeProductDepartment(Request $request): void
    {
        abort_unless($request->user()?->department?->code === 'market_research' || $request->user()?->hasRole('administrator'), 403);
    }
}
