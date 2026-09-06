<?php

namespace Tests\Feature\Projects;

use App\Models\Department;
use App\Models\CreativeAsset;
use App\Models\ProductProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreativeAssetTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_creative_can_open_the_manual_asset_entry_form(): void
    {
        $department = Department::factory()->create(['code' => 'content_creative']);
        $user = User::factory()->create(['department_id' => $department->id, 'role' => 'member']);
        $project = ProductProject::create([
            'project_code' => 'PP-202609-ASSET02',
            'product_name' => '星空投影灯',
            'market' => 'US',
            'priority' => 'high',
            'current_stage' => 'content_creative',
            'status' => 'in_progress',
            'owner_department_id' => $department->id,
            'owner_user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get("/projects?stage=content_creative&project={$project->id}")
            ->assertOk()
            ->assertSee('创意部重点工作')
            ->assertSee('参考素材链接')
            ->assertSee('脚本或核心卖点')
            ->assertSee('视频（广告投放）')
            ->assertSee('动图（详情页）')
            ->assertDontSee('value="image"', false)
            ->assertSee('保存素材');
    }

    public function test_content_creative_can_upload_a_video_asset_for_a_product_project(): void
    {
        Storage::fake('local');
        $department = Department::factory()->create(['code' => 'content_creative']);
        $user = User::factory()->create(['department_id' => $department->id, 'role' => 'member']);
        $project = ProductProject::create([
            'project_code' => 'PP-202609-ASSET01',
            'product_name' => '星空投影灯',
            'market' => 'US',
            'priority' => 'high',
            'current_stage' => 'content_creative',
            'status' => 'in_progress',
            'owner_department_id' => $department->id,
            'owner_user_id' => $user->id,
            'created_by' => $user->id,
        ]);
        $file = UploadedFile::fake()->create('projector-demo.mp4', 1024, 'video/mp4');

        $this->actingAs($user)
            ->post("/projects/{$project->id}/creative-assets", [
                'title' => '投影效果演示 V1',
                'asset_types' => ['video'],
                'source_type' => 'tiktok',
                'asset_file' => $file,
                'reference_urls' => ['https://tiktok.com/@example/video/1'],
                'copy_text' => '突出夜间投影效果与使用场景。',
                'notes' => '突出夜间投影效果。',
            ])
            ->assertRedirect(route('projects.index', ['stage' => 'content_creative', 'project' => $project]));

        $this->assertDatabaseHas('creative_assets', [
            'product_project_id' => $project->id,
            'title' => '星空投影灯',
            'asset_type' => 'video',
            'source_type' => 'tiktok',
            'status' => 'draft',
            'storage_disk' => 'local',
        ]);
        $storedPath = DB::table('creative_assets')->where('product_project_id', $project->id)->value('storage_path');
        Storage::disk('local')->assertExists($storedPath);
        $this->assertDatabaseHas('project_activities', ['product_project_id' => $project->id, 'event' => 'creative_asset.created']);

        $asset = CreativeAsset::query()->where('product_project_id', $project->id)->firstOrFail();
        $this->actingAs($user)
            ->get("/projects/{$project->id}/creative-assets/{$asset->id}/download")
            ->assertOk();
    }

    public function test_content_creative_can_save_multiple_asset_types_including_gif_and_youtube_reference(): void
    {
        $department = Department::factory()->create(['code' => 'content_creative']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-GIF', 'product_name' => '动图素材产品', 'market' => 'US', 'priority' => 'medium', 'current_stage' => 'content_creative', 'status' => 'in_progress', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);

        $this->actingAs($user)->post("/projects/{$project->id}/creative-assets", ['title' => '视频与动图素材 V1', 'asset_types' => ['video', 'gif'], 'source_type' => 'youtube', 'reference_urls' => ['https://youtube.com/watch?v=demo'], 'copy_text' => '突出动图节奏与产品卖点。'])
            ->assertRedirect(route('projects.index', ['stage' => 'content_creative', 'project' => $project]));

        $this->assertDatabaseHas('creative_assets', ['product_project_id' => $project->id, 'asset_type' => 'video', 'source_type' => 'youtube']);
    }

    public function test_content_creative_requires_script_and_one_or_more_reference_links(): void
    {
        $department = Department::factory()->create(['code' => 'content_creative']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-REFERENCES', 'product_name' => '参考素材产品', 'market' => 'US', 'priority' => 'medium', 'current_stage' => 'content_creative', 'status' => 'in_progress', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);

        $this->actingAs($user)
            ->from("/projects?stage=content_creative&project={$project->id}")
            ->post("/projects/{$project->id}/creative-assets", ['asset_types' => ['gif'], 'source_type' => 'tiktok'])
            ->assertSessionHasErrors(['reference_urls', 'copy_text']);
    }

    public function test_content_creative_only_accepts_video_or_gif_asset_types(): void
    {
        $department = Department::factory()->create(['code' => 'content_creative']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-CREATIVE-TYPES', 'product_name' => '素材类型产品', 'market' => 'US', 'priority' => 'medium', 'current_stage' => 'content_creative', 'status' => 'in_progress', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);

        $this->actingAs($user)
            ->from("/projects?stage=content_creative&project={$project->id}")
            ->post("/projects/{$project->id}/creative-assets", [
                'asset_types' => ['image'],
                'source_type' => 'tiktok',
                'reference_urls' => ['https://tiktok.com/@example/video/1'],
                'copy_text' => '不应接受图片类型。',
            ])
            ->assertSessionHasErrors(['asset_types.0']);
    }

    public function test_content_creative_saves_multiple_reference_links_for_one_asset(): void
    {
        $department = Department::factory()->create(['code' => 'content_creative']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-MULTI-REFERENCES', 'product_name' => '多参考链接产品', 'market' => 'US', 'priority' => 'medium', 'current_stage' => 'content_creative', 'status' => 'in_progress', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);

        $this->actingAs($user)->post("/projects/{$project->id}/creative-assets", [
            'asset_types' => ['gif'],
            'source_type' => 'tiktok',
            'reference_urls' => ['https://tiktok.com/@example/video/1', 'https://youtube.com/watch?v=example'],
            'copy_text' => '突出产品使用前后对比与核心卖点。',
        ])->assertRedirect(route('projects.index', ['stage' => 'content_creative', 'project' => $project]));

        $asset = CreativeAsset::query()->where('product_project_id', $project->id)->firstOrFail();
        $this->assertSame(['https://tiktok.com/@example/video/1', 'https://youtube.com/watch?v=example'], $asset->reference_urls);
        $this->assertSame('突出产品使用前后对比与核心卖点。', $asset->copy_text);
    }
}
