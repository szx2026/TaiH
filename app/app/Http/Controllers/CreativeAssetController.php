<?php

namespace App\Http\Controllers;

use App\Actions\Activity\RecordProjectActivity;
use App\Models\CreativeAsset;
use App\Models\ProductProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CreativeAssetController extends Controller
{
    public function download(Request $request, ProductProject $project, CreativeAsset $asset): StreamedResponse
    {
        abort_unless($asset->product_project_id === $project->id, 404);
        abort_unless($asset->hasStoredFile(), 404);

        return Storage::disk($asset->storage_disk)->download($asset->storage_path, basename($asset->storage_path));
    }

    public function store(Request $request, ProductProject $project): RedirectResponse
    {
        abort_unless($request->user()?->department?->code === 'content_creative' || $request->user()?->hasRole('administrator'), 403);

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'asset_types' => ['required', 'array', 'min:1'],
            'asset_types.*' => ['required', 'distinct', Rule::in(['video', 'gif', 'archive'])],
            'source_type' => ['required', Rule::in(['tiktok', 'youtube', 'other'])],
            'landing_page_id' => ['nullable', 'integer', Rule::exists('landing_pages', 'id')],
            'asset_file' => ['nullable', 'file', 'max:262144'],
            'asset_files' => ['nullable', 'array'],
            'asset_files.*' => ['file', 'max:262144'],
            'reference_urls' => ['required', 'array', 'min:1'],
            'reference_urls.*' => ['required', 'distinct', 'url', 'max:2048'],
            'copy_text' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        if (! empty($data['landing_page_id'])) {
            abort_unless($project->landingPages()->whereKey($data['landing_page_id'])->exists(), 422);
        }

        $files = [];
        if ($request->hasFile('asset_files')) {
            $uploaded = $request->file('asset_files');
            $files = is_array($uploaded) ? $uploaded : [$uploaded];
        } elseif ($request->hasFile('asset_file')) {
            $files = [$request->file('asset_file')];
        }

        if (empty($files)) {
            $asset = CreativeAsset::create([
                'product_project_id' => $project->id,
                'title' => $project->product_name,
                'asset_type' => $data['asset_types'][0],
                'asset_types' => $data['asset_types'],
                'source_type' => $data['source_type'],
                'landing_page_id' => $data['landing_page_id'] ?? null,
                'external_url' => $data['reference_urls'][0],
                'reference_urls' => array_values($data['reference_urls']),
                'storage_disk' => null,
                'storage_path' => null,
                'copy_text' => $data['copy_text'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'draft',
                'created_by' => $request->user()->id,
            ]);
            app(RecordProjectActivity::class)->handle($project, $request->user(), 'creative_asset.created', ['asset_id' => $asset->id, 'title' => $asset->title]);
        } else {
            foreach ($files as $file) {
                $ext = strtolower($file->getClientOriginalExtension());
                $types = $data['asset_types'];
                if (in_array($ext, ['zip', 'rar', '7z', 'tar', 'gz'], true) && ! in_array('archive', $types, true)) {
                    $types[] = 'archive';
                }

                $path = $file->store('creative-assets/'.$project->id, 'local');
                $title = count($files) > 1 ? $project->product_name.' - '.$file->getClientOriginalName() : $project->product_name;

                $asset = CreativeAsset::create([
                    'product_project_id' => $project->id,
                    'title' => $title,
                    'asset_type' => $types[0],
                    'asset_types' => $types,
                    'source_type' => $data['source_type'],
                    'landing_page_id' => $data['landing_page_id'] ?? null,
                    'external_url' => $data['reference_urls'][0],
                    'reference_urls' => array_values($data['reference_urls']),
                    'storage_disk' => 'local',
                    'storage_path' => $path,
                    'copy_text' => $data['copy_text'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'status' => 'draft',
                    'created_by' => $request->user()->id,
                ]);
                app(RecordProjectActivity::class)->handle($project, $request->user(), 'creative_asset.created', ['asset_id' => $asset->id, 'title' => $asset->title]);
            }
        }

        return $this->redirectWithFilters($request, 'projects.index', ['stage' => 'content_creative', 'project' => $project]);
    }

    public function destroy(Request $request, ProductProject $project, CreativeAsset $asset): RedirectResponse
    {
        abort_unless($request->user()?->department?->code === 'content_creative' || $request->user()?->hasRole('administrator'), 403);
        abort_unless($asset->product_project_id === $project->id, 404);

        if ($asset->hasStoredFile()) {
            Storage::disk($asset->storage_disk)->delete($asset->storage_path);
        }

        $title = $asset->title;
        $asset->delete();

        app(RecordProjectActivity::class)->handle($project, $request->user(), 'creative_asset.deleted', ['title' => $title]);

        return $this->redirectWithFilters($request, 'projects.index', ['stage' => 'content_creative', 'project' => $project]);
    }
}

