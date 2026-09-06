<?php

namespace Tests\Feature\Projects;

use App\Models\Department;
use App\Models\ProductProject;
use App\Models\ProductSku;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_website_operations_can_open_the_manual_landing_page_form(): void
    {
        $department = Department::factory()->create(['code' => 'website_operations']);
        $user = User::factory()->create(['department_id' => $department->id, 'role' => 'member']);
        $project = ProductProject::create([
            'project_code' => 'PP-202609-LANDING02',
            'product_name' => '星空投影灯',
            'market' => 'US',
            'priority' => 'high',
            'current_stage' => 'website_operations',
            'status' => 'in_progress',
            'owner_department_id' => $department->id,
            'owner_user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get("/projects?stage=website_operations&project={$project->id}")
            ->assertOk()
            ->assertSee('SKU 与 Shopify 产品')
            ->assertSee('Shopify 产品或落地页链接');
    }

    public function test_website_operations_can_create_a_landing_page_with_price_specs_and_skus(): void
    {
        $department = Department::factory()->create(['code' => 'website_operations']);
        $user = User::factory()->create(['department_id' => $department->id, 'role' => 'member']);
        $project = ProductProject::create([
            'project_code' => 'PP-202609-LANDING01',
            'product_name' => '星空投影灯',
            'market' => 'US',
            'priority' => 'high',
            'current_stage' => 'website_operations',
            'status' => 'in_progress',
            'owner_department_id' => $department->id,
            'owner_user_id' => $user->id,
            'created_by' => $user->id,
        ]);
        $sku = ProductSku::create([
            'product_project_id' => $project->id,
            'product_source_id' => null,
            'sku_code' => 'NC03342609026143',
            'variant_name' => '夜灯+3影片',
            'sku_status' => 'imported',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->post("/projects/{$project->id}/landing-pages", [
                'title' => '星空投影灯 - 美国站详情页',
                'page_url' => 'https://shop.example.com/products/star-projector',
                'selling_price' => 39.99,
                'currency' => 'USD',
                'specifications' => '夜灯，含 3 张投影片',
                'sku_ids' => [$sku->id],
            ])
            ->assertRedirect(route('projects.index', ['stage' => 'website_operations', 'project' => $project]));

        $this->assertDatabaseHas('landing_pages', [
            'product_project_id' => $project->id,
            'version' => 1,
            'title' => '星空投影灯',
            'page_url' => 'https://shop.example.com/products/star-projector',
            'selling_price' => null,
            'status' => 'draft',
        ]);
        $this->assertDatabaseHas('landing_page_skus', ['product_sku_id' => $sku->id]);
        $this->assertDatabaseHas('product_skus', ['id' => $sku->id, 'sku_status' => 'used_on_page']);
        $activity = $project->activities()->where('event', 'landing_page.created')->latest()->first();
        $this->assertNotNull($activity);
        $this->assertSame([
            'landing_page_id' => $activity->payload['landing_page_id'],
            'title' => '星空投影灯',
            'shopify_product_linked' => true,
            'sku_count' => 1,
        ], $activity->payload);
    }

    public function test_website_operations_uses_product_name_automatically_for_shopify_page_title(): void
    {
        $department = Department::factory()->create(['code' => 'website_operations']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-AUTO-TITLE', 'product_name' => '自动命名产品', 'market' => 'US', 'priority' => 'medium', 'current_stage' => 'website_operations', 'status' => 'in_progress', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);
        $sku = ProductSku::create(['product_project_id' => $project->id, 'sku_code' => 'AUTO-TITLE-SKU', 'variant_name' => '默认规格', 'sku_status' => 'imported', 'created_by' => $user->id]);

        $this->actingAs($user)->post("/projects/{$project->id}/landing-pages", ['page_url' => 'https://shop.example.com/products/auto-title', 'sku_ids' => [$sku->id]])
            ->assertRedirect(route('projects.index', ['stage' => 'website_operations', 'project' => $project]));

        $this->assertDatabaseHas('landing_pages', ['product_project_id' => $project->id, 'title' => '自动命名产品']);
    }
}
