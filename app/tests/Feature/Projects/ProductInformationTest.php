<?php

namespace Tests\Feature\Projects;

use App\Models\Department;
use App\Models\ProductProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_department_saves_keywords_and_a_detail_page_reference(): void
    {
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-PRODUCT-INFO', 'product_name' => '彩色轮胎灯', 'market' => 'US', 'priority' => 'medium', 'current_stage' => 'market_research', 'status' => 'draft', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);

        $this->actingAs($user)->post("/projects/{$project->id}/research-sources", [
            'platform' => 'tiktok',
            'url' => 'https://www.tiktok.com/example-product',
            'evidence_note' => '用于验证产品资料保存。',
            'keywords' => '轮胎灯, 彩色轮毂灯',
            'detail_reference_url' => 'https://example.com/products/wheel-light',
        ])->assertRedirect(route('projects.index', ['stage' => 'market_research', 'project' => $project]));

        $this->assertDatabaseHas('product_projects', [
            'id' => $project->id,
            'keywords' => '轮胎灯, 彩色轮毂灯',
            'detail_reference_url' => 'https://example.com/products/wheel-light',
        ]);

        $this->actingAs($user)->get("/projects?stage=market_research&project={$project->id}")
            ->assertOk()
            ->assertSee('彩色轮胎灯 · 轮胎灯, 彩色轮毂灯')
            ->assertSee('https://example.com/products/wheel-light', false);
    }
}
