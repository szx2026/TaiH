<?php

namespace App\Http\Controllers;

use App\Actions\Activity\RecordProjectActivity;
use App\Models\ProductProject;
use App\Models\ProductSource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductSourceController extends Controller
{
    public function store(Request $request, ProductProject $project): RedirectResponse
    {
        abort_unless($request->user()?->department?->code === 'website_operations', 403);

        $data = $request->validate([
            'supplier_url' => ['required', 'url', 'max:2048'],
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'weight_g' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:4000'],
            'skus' => ['required', 'array', 'min:1'],
            'skus.*.sku_code' => ['required', 'string', 'max:100'],
            'skus.*.variant_name' => ['required', 'string', 'max:255'],
            'skus.*.sku_status' => ['nullable', Rule::in(['proposed', 'pending_creation', 'created', 'imported', 'used_on_page', 'inactive'])],
        ]);

        DB::transaction(function () use ($data, $project, $request): void {
            $source = ProductSource::create([
                'product_project_id' => $project->id,
                'supplier_url' => $data['supplier_url'],
                'supplier_name' => $data['supplier_name'] ?? null,
                'purchase_price' => $data['purchase_price'] ?? null,
                'currency' => strtoupper($data['currency']),
                'weight_g' => $data['weight_g'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            foreach ($data['skus'] as $sku) {
                $source->skus()->create([
                    'product_project_id' => $project->id,
                    'sku_code' => $sku['sku_code'],
                    'variant_name' => $sku['variant_name'],
                    'sku_status' => $sku['sku_status'] ?? 'imported',
                    'created_by' => $request->user()->id,
                ]);
            }

            app(RecordProjectActivity::class)->handle($project, $request->user(), 'supplier_source.created', [
                'source_id' => $source->id,
                'supplier_url' => $source->supplier_url,
                'sku_count' => count($data['skus']),
            ]);
        });

        return to_route('projects.workspace', ['project' => $project, 'tab' => 'operations']);
    }
}
