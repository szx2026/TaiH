<?php

namespace App\Http\Controllers;

use App\Actions\Activity\RecordProjectActivity;
use App\Models\CreativeAsset;
use App\Models\ProductProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CreativeAssetController extends Controller
{
    public function download(Request $request, ProductProject $project, CreativeAsset $asset): BinaryFileResponse
    {
        abort_unless($asset->product_project_id === $project->id, 404);
        abort_unless($asset->storage_path && $asset->storage_disk, 404);

        return Storage::disk($asset->storage_disk)->download($asset->storage_path, basename($asset->storage_path));
    }

    public function store(Request $request, ProductProject $project): RedirectResponse
    {
        abort_unless($request->user()?->department?->code === 'content_creative' || $request->user()?->hasRole('administrator'), 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'asset_types' => ['required', 'array', 'min:1'],
            'asset_types.*' => ['required', 'distinct', Rule::in(['video', 'image', 'gif', 'copy'])],
            'source_type' => ['required', Rule::in(['tiktok', 'youtube', 'other'])],
            'landing_page_id' => ['nullable', 'integer', Rule::exists('landing_pages', 'id')],
            'asset_file' => ['nullable', 'file', 'max:102400'],
            'external_url' => ['nullable', 'url', 'max:2048'],
            'copy_text' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        if (! $request->hasFile('asset_file') && empty($data['external_url']) && empty($data['copy_text'])) {
            return back()->withErrors(['asset_file' => '请上传文件、填写素材链接或录入文案。']);
        }

        if (! empty($data['landing_page_id'])) {
            abort_unless($project->landingPages()->whereKey($data['landing_page_id'])->exists(), 422);
        }

        $path = $request->file('asset_file')?->store('creative-assets/'.$project->id, 'local');

        $asset = CreativeAsset::create([
            'product_project_id' => $project->id,
            'title' => $data['title'],
            'asset_type' => $data['asset_types'][0],
            'asset_types' => $data['asset_types'],
            'source_type' => $data['source_type'],
            'landing_page_id' => $data['landing_page_id'] ?? null,
            'external_url' => $data['external_url'] ?? null,
            'storage_disk' => $path ? 'local' : null,
            'storage_path' => $path,
            'copy_text' => $data['copy_text'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'draft',
            'created_by' => $request->user()->id,
        ]);
        app(RecordProjectActivity::class)->handle($project, $request->user(), 'creative_asset.created', ['asset_id' => $asset->id, 'title' => $asset->title]);

        return to_route('projects.index', ['stage' => 'content_creative', 'project' => $project]);
    }
}
