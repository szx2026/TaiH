<?php

namespace Tests\Feature\Projects;

use App\Models\Department;
use App\Models\ProductProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductProjectWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_department_creates_a_us_project_with_image_date_code_and_business_stage(): void
    {
        Storage::fake('public');
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id]);

        $this->actingAs($user)->post('/projects', [
            'product_name' => '便携榨汁杯',
            'category' => '厨房用品',
            'priority' => 'market_new',
            'product_image' => UploadedFile::fake()->create('juicer.jpg', 100, 'image/jpeg'),
        ])->assertRedirect();

        $project = ProductProject::query()->firstOrFail();
        $this->assertSame('US', $project->market);
        $this->assertSame('market_new', $project->priority);
        $this->assertStringStartsWith('PP-'.now()->format('Ymd').'-', $project->project_code);
        $this->assertNotNull($project->released_at);
        Storage::disk('public')->assertExists($project->product_image_path);
    }

    public function test_product_stage_must_be_chosen_explicitly(): void
    {
        Storage::fake('public');
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id]);

        $this->actingAs($user)->post('/projects', [
            'product_name' => '未选阶段产品', 'category' => '厨房用品',
            'product_image' => UploadedFile::fake()->create('product.jpg', 100, 'image/jpeg'),
        ])->assertSessionHasErrors('priority');
    }

    public function test_product_department_can_replace_a_product_main_image(): void
    {
        Storage::fake('public');
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create([
            'project_code' => 'PP-202609-IMAGE-UPDATE',
            'product_name' => '可更换主图产品',
            'product_image_path' => 'product-projects/old-image.jpg',
            'market' => 'US',
            'priority' => 'market_new',
            'current_stage' => 'market_research',
            'status' => 'draft',
            'owner_department_id' => $department->id,
            'owner_user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->patch("/projects/{$project->id}/image", [
                'product_image' => UploadedFile::fake()->create('replacement.webp', 100, 'image/webp'),
            ])
            ->assertRedirect(route('projects.index', ['stage' => 'market_research', 'project' => $project]));

        $project->refresh();
        $this->assertNotSame('product-projects/old-image.jpg', $project->product_image_path);
        Storage::disk('public')->assertExists($project->product_image_path);
    }
}
