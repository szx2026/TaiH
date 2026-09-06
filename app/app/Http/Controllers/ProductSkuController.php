<?php

namespace App\Http\Controllers;

use App\Actions\Activity\RecordProjectActivity;
use App\Models\ProductProject;
use App\Models\ProductSku;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'product_sku_id' => ['nullable', Rule::exists('product_skus', 'id')->where('product_project_id', $project->id)],
            'sku_code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('product_skus')->where('product_project_id', $project->id),
            ],
            'variant_name' => ['nullable', 'string', 'max:255'],
        ]);

        $skuQuery = $project->skus()
            ->whereNull('sku_code')
            ->when($data['product_sku_id'] ?? null, fn ($query, $skuId) => $query->whereKey($skuId));

        if (empty($data['product_sku_id']) && ! empty($data['variant_name'])) {
            $skuQuery->where('variant_name', $data['variant_name']);
        }

        $sku = $skuQuery->firstOrFail();

        $sku->update([
            'sku_code' => $data['sku_code'],
            'sku_status' => 'internal_confirmed',
        ]);

        app(RecordProjectActivity::class)->handle($project, $request->user(), 'sku.imported_from_product_system', [
            'sku_id' => $sku->id,
            'sku_code' => $sku->sku_code,
        ]);

        return to_route('projects.index', ['stage' => 'market_research', 'project' => $project]);
    }

    public function destroy(Request $request, ProductProject $project, ProductSku $sku): RedirectResponse
    {
        abort_unless(
            $request->user()?->department?->code === 'market_research'
                || $request->user()?->hasRole('administrator'),
            403,
        );
        abort_unless($sku->product_project_id === $project->id, 404);

        DB::transaction(function () use ($project, $request, $sku): void {
            $sku->load('source');
            $sku->landingPages()->detach();

            app(RecordProjectActivity::class)->handle($project, $request->user(), 'sku.deleted', [
                'sku_id' => $sku->id,
                'sku_code' => $sku->sku_code,
                'variant_name' => $sku->variant_name,
            ]);

            $sku->delete();
        });

        return to_route('projects.index', ['stage' => 'market_research', 'project' => $project]);
    }
}
