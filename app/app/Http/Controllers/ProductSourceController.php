<?php

namespace App\Http\Controllers;

use App\Actions\Activity\RecordProjectActivity;
use App\Models\ProductProject;
use App\Models\ProductSource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
        ]);

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

        app(RecordProjectActivity::class)->handle($project, $request->user(), 'supplier_source.created', [
            'source_id' => $source->id,
            'supplier_url' => $source->supplier_url,
        ]);

        return to_route('projects.index', ['stage' => 'website_operations', 'project' => $project]);
    }
}
