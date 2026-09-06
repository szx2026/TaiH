<?php

namespace App\Http\Controllers;

use App\Actions\Activity\RecordProjectActivity;
use App\Models\LandingPage;
use App\Models\ProductProject;
use App\Models\ProductSku;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LandingPageController extends Controller
{
    public function store(Request $request, ProductProject $project): RedirectResponse
    {
        abort_unless($request->user()?->department?->code === 'website_operations' || $request->user()?->hasRole('administrator'), 403);

        $data = $request->validate([
            'page_url' => ['required', 'url', 'max:2048'],
            'detail_image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $skuIds = $project->skus()->pluck('id');

        DB::transaction(function () use ($data, $project, $request, $skuIds): void {
            $version = (int) $project->landingPages()->max('version') + 1;
            $page = LandingPage::create([
                'product_project_id' => $project->id,
                'version' => $version,
                'title' => $project->product_name,
                'page_url' => $data['page_url'],
                'detail_image_path' => $data['detail_image']->store('landing-pages', 'public'),
                'selling_price' => null,
                'currency' => 'USD',
                'specifications' => null,
                'status' => 'draft',
                'created_by' => $request->user()->id,
            ]);

            $page->skus()->sync($skuIds);
            ProductSku::query()->whereIn('id', $skuIds)->update(['sku_status' => 'used_on_page']);
            app(RecordProjectActivity::class)->handle($project, $request->user(), 'landing_page.created', [
                'landing_page_id' => $page->id,
                'title' => $page->title,
                'shopify_product_linked' => true,
                'sku_count' => $skuIds->count(),
            ]);
        });

        return to_route('projects.index', ['stage' => 'website_operations', 'project' => $project]);
    }
}
