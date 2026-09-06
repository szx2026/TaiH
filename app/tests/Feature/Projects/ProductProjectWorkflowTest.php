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
}
