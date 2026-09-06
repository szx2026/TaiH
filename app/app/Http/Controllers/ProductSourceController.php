<?php

namespace App\Http\Controllers;

use App\Actions\Activity\RecordProjectActivity;
use App\Models\ProductProject;
use App\Models\ProductSource;
use App\Models\ProductSku;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductSourceController extends Controller
{
    public function store(Request $request, ProductProject $project): RedirectResponse
    {
        abort_unless($request->user()?->department?->code === 'market_research' || $request->user()?->hasRole('administrator'), 403);

        $data = $request->validate([
            'supplier_url' => ['required', 'url', 'max:2048'],
            'supplier_name' => ['required', 'string', 'max:255'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'weight_g' => ['nullable', 'integer', 'min:0'],
            'notes' => ['required', 'string', 'max:4000'],
            'variant_name' => ['required', 'string', 'max:255'],
        ]);

        $source = DB::transaction(function () use ($data, $project, $request): ProductSource {
            $source = ProductSource::firstOrCreate(
                ['product_project_id' => $project->id, 'supplier_url' => $data['supplier_url'], 'supplier_name' => $data['supplier_name']],
                [
                    'purchase_price' => $data['purchase_price'] ?? null,
                    'currency' => strtoupper($data['currency']),
                    'weight_g' => $data['weight_g'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'created_by' => $request->user()->id,
                ],
            );
            ProductSku::create([
                'product_project_id' => $project->id,
                'product_source_id' => $source->id,
                'sku_code' => null,
                'variant_name' => $data['variant_name'],
                'purchase_price' => $data['purchase_price'] ?? null,
                'weight_g' => $data['weight_g'] ?? null,
                'sku_status' => 'source_recorded',
                'created_by' => $request->user()->id,
            ]);

            return $source;
        });

        app(RecordProjectActivity::class)->handle($project, $request->user(), 'supplier_source.created', [
            'source_id' => $source->id,
            'supplier_url' => $source->supplier_url,
            'variant_name' => $data['variant_name'],
        ]);

        return to_route('projects.index', ['stage' => 'market_research', 'project' => $project]);
    }
}
