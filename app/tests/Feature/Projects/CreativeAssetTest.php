<?php

namespace Tests\Feature\Projects;

use App\Models\Department;
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
            ->get("/projects/{$project->id}")
            ->assertOk()
            ->assertSee('上传素材')
            ->assertSee('素材来源');
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
                'asset_type' => 'video',
                'source_type' => 'original',
                'asset_file' => $file,
                'notes' => '突出夜间投影效果。',
            ])
            ->assertRedirect('/projects');

        $this->assertDatabaseHas('creative_assets', [
            'product_project_id' => $project->id,
            'title' => '投影效果演示 V1',
            'asset_type' => 'video',
            'source_type' => 'original',
            'status' => 'draft',
            'storage_disk' => 'local',
        ]);
        $storedPath = DB::table('creative_assets')->where('product_project_id', $project->id)->value('storage_path');
        Storage::disk('local')->assertExists($storedPath);
    }
}
