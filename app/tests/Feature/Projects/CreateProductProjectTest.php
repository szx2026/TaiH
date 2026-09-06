<?php

namespace Tests\Feature\Projects;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreateProductProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_market_research_member_can_create_a_draft_product_project(): void
    {
        Storage::fake('public');
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create([
            'department_id' => $department->id,
            'role' => 'member',
        ]);

        $response = $this->actingAs($user)->post('/projects', [
            'product_name' => '星空投影灯',
            'category' => '家居装饰',
            'priority' => 'market_new',
            'product_image' => UploadedFile::fake()->create('project.jpg', 100, 'image/jpeg'),
        ]);

        $project = \App\Models\ProductProject::query()->where('product_name', '星空投影灯')->firstOrFail();
        $response->assertRedirect(route('projects.index', ['stage' => 'market_research', 'project' => $project]));

        $this->assertDatabaseHas('product_projects', [
            'product_name' => '星空投影灯',
            'current_stage' => 'market_research',
            'status' => 'draft',
            'owner_user_id' => $user->id,
        ]);
    }

    public function test_product_project_creation_rejects_non_us_market(): void
    {
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id, 'role' => 'member']);

        $this->actingAs($user)
            ->from('/projects?stage=market_research')
            ->post('/projects', [
                'product_name' => '英国测试产品',
                'category' => '家居用品',
                'market' => 'UK',
                'priority' => 'market_new',
                'product_image' => UploadedFile::fake()->create('project.jpg', 100, 'image/jpeg'),
            ])
            ->assertRedirect('/projects?stage=market_research')
            ->assertSessionHasErrors('market');

        $this->assertDatabaseMissing('product_projects', ['product_name' => '英国测试产品']);
    }

    public function test_business_creation_time_is_saved_and_used_by_the_project_date_range_filter(): void
    {
        Storage::fake('public');
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id, 'role' => 'member']);

        $this->actingAs($user)->post('/projects', [
            'product_name' => '日期筛选产品', 'category' => '家居用品', 'priority' => 'market_new',
            'created_at_business' => '2026-08-15T10:30',
            'product_image' => UploadedFile::fake()->create('project.jpg', 100, 'image/jpeg'),
        ])->assertRedirect();

        $this->assertDatabaseHas('product_projects', [
            'product_name' => '日期筛选产品',
            'released_at' => '2026-08-15 10:30:00',
        ]);

        $outsideProject = \App\Models\ProductProject::create([
            'project_code' => 'PP-202609-OUTSIDE', 'product_name' => '范围外产品', 'market' => 'US',
            'priority' => 'market_new', 'current_stage' => 'market_research', 'status' => 'draft',
            'released_at' => '2026-09-01 10:30:00', 'owner_department_id' => $department->id,
            'owner_user_id' => $user->id, 'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get('/projects?stage=market_research&created_from=2026-08-01&created_to=2026-08-31')
            ->assertOk()
            ->assertSee('日期筛选产品')
            ->assertDontSee($outsideProject->product_name);
    }
}
