<?php

namespace App\Http\Controllers;

use App\Actions\Activity\RecordProjectActivity;
use App\Models\ProductProject;
use App\Models\ProductSku;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductSkuController extends Controller
{
    public function store(Request $request, ProductProject $project): RedirectResponse
    {
        abort_unless(
            $request->user()?->department?->code === 'market_research'
                || $request->user()?->hasRole('administrator'),
            403,
        );

        $data = $request->validate([
            'sku_code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('product_skus')->where('product_project_id', $project->id),
            ],
            'variant_name' => ['required', 'string', 'max:255'],
        ]);

        $sku = ProductSku::create([
            'product_project_id' => $project->id,
            'product_source_id' => null,
            'sku_code' => $data['sku_code'],
            'variant_name' => $data['variant_name'],
            'sku_status' => 'imported',
            'created_by' => $request->user()->id,
        ]);

        app(RecordProjectActivity::class)->handle($project, $request->user(), 'sku.imported_from_product_system', [
            'sku_id' => $sku->id,
            'sku_code' => $sku->sku_code,
        ]);

        return to_route('projects.index', ['stage' => 'market_research', 'project' => $project]);
    }
}
