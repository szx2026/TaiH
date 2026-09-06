<?php

namespace App\Http\Controllers;

use App\Actions\Activity\RecordProjectActivity;
use App\Models\ProductProject;
use App\Models\ProjectDecision;
use App\Models\ProductSource;
use App\Models\ProductSku;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductSourceController extends Controller
{
    public function store(Request $request, ProductProject $project): RedirectResponse
    {
        abort_unless($request->user()?->department?->code === 'market_research' || $request->user()?->hasRole('administrator'), 403);

        $data = $request->validate([
            'supplier_url' => ['required', 'url', 'max:2048'],
            'supplier_name' => ['required', 'string', 'max:255'],
            'currency' => ['required', 'string', 'size:3'],
            'notes' => ['required', 'string', 'max:4000'],
            'product_name' => ['required', 'string', 'max:255'],
            'alternative_sources' => ['nullable', 'array'],
            'alternative_sources.*.supplier_url' => ['required', 'url', 'max:2048'],
            'alternative_sources.*.supplier_name' => ['required', 'string', 'max:255'],
            'specifications' => ['required', 'array', 'min:1'],
            'specifications.*.sku_code' => ['required', 'string', 'max:100', Rule::unique('product_skus')->where('product_project_id', $project->id)],
            'specifications.*.variant_name' => ['required', 'string', 'max:255'],
            'specifications.*.purchase_price' => ['nullable', 'numeric', 'min:0'],
            'specifications.*.weight_g' => ['nullable', 'integer', 'min:0'],
        ]);

        $source = DB::transaction(function () use ($data, $project, $request): ProductSource {
            $source = ProductSource::firstOrCreate(
                ['product_project_id' => $project->id, 'supplier_url' => $data['supplier_url'], 'supplier_name' => $data['supplier_name']],
                [
                    'currency' => strtoupper($data['currency']),
                    'product_name' => $data['product_name'],
                    'notes' => $data['notes'] ?? null,
                    'created_by' => $request->user()->id,
                ],
            );
            $source->update([
                'product_name' => $data['product_name'],
                'currency' => strtoupper($data['currency']),
                'notes' => $data['notes'],
            ]);
            foreach ($data['alternative_sources'] ?? [] as $alternativeSource) {
                $alternative = ProductSource::firstOrCreate(
                    [
                        'product_project_id' => $project->id,
                        'supplier_url' => $alternativeSource['supplier_url'],
                        'supplier_name' => $alternativeSource['supplier_name'],
                    ],
                    [
                        'currency' => strtoupper($data['currency']),
                        'product_name' => $data['product_name'],
                        'notes' => $data['notes'],
                        'created_by' => $request->user()->id,
                    ],
                );
                $alternative->update([
                    'product_name' => $data['product_name'],
                    'currency' => strtoupper($data['currency']),
                    'notes' => $data['notes'],
                ]);
            }
            foreach ($data['specifications'] as $specification) {
                ProductSku::create([
                    'product_project_id' => $project->id,
                    'product_source_id' => $source->id,
                    'sku_code' => $specification['sku_code'],
                    'variant_name' => $specification['variant_name'],
                    'purchase_price' => $specification['purchase_price'] ?? null,
                    'weight_g' => $specification['weight_g'] ?? null,
                    'sku_status' => 'internal_confirmed',
                    'created_by' => $request->user()->id,
                ]);
            }

            return $source;
        });

        $decision = ProjectDecision::firstOrCreate(
            [
                'product_project_id' => $project->id,
                'decision_type' => 'specification',
                'requested_from_stage' => 'website_operations',
                'status' => 'open',
            ],
            [
                'title' => "确认「{$project->product_name}」初步产品规格",
                'details' => [
                    'initial_specifications' => ProductSku::query()
                        ->where('product_project_id', $project->id)
                        ->whereIn('sku_code', collect($data['specifications'])->pluck('sku_code'))
                        ->get()
                        ->map(fn (ProductSku $sku) => [
                            'sku_id' => $sku->id,
                            'sku_code' => $sku->sku_code,
                            'variant_name' => $sku->variant_name,
                            'purchase_price' => $sku->purchase_price,
                            'weight_g' => $sku->weight_g,
                        ])->values()->all(),
                ],
                'created_by' => $request->user()->id,
            ],
        );

        app(RecordProjectActivity::class)->handle($project, $request->user(), 'supplier_source.created', [
            'source_id' => $source->id,
            'supplier_url' => $source->supplier_url,
            'product_name' => $source->product_name,
            'specifications' => count($data['specifications']),
        ]);

        if ($decision->wasRecentlyCreated) {
            app(RecordProjectActivity::class)->handle($project, $request->user(), 'decision.created', [
                'decision_id' => $decision->id,
                'decision_type' => $decision->decision_type,
                'requested_from_stage' => $decision->requested_from_stage,
                'title' => $decision->title,
            ]);
        }

        return to_route('projects.index', ['stage' => 'market_research', 'project' => $project]);
    }
}
