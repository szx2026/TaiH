<?php

namespace Tests\Feature\Projects;

use App\Models\Department;
use App\Models\LandingPage;
use App\Models\ProductProject;
use App\Models\ProductSku;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
            ->assertSee('最终产品规格与 Shopify 产品')
            ->assertSee('Shopify 产品或落地页链接');
    }

    public function test_website_operations_automatically_associates_a_landing_page_with_all_project_skus(): void
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
        $secondSku = ProductSku::create([
            'product_project_id' => $project->id,
            'product_source_id' => null,
            'sku_code' => 'NC03342609026144',
            'variant_name' => '夜灯+5影片',
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
                'detail_image' => UploadedFile::fake()->create('detail.jpg', 100, 'image/jpeg'),
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
        $this->assertDatabaseHas('landing_page_skus', ['product_sku_id' => $secondSku->id]);
        $this->assertDatabaseHas('product_skus', ['id' => $sku->id, 'sku_status' => 'used_on_page']);
        $this->assertDatabaseHas('product_skus', ['id' => $secondSku->id, 'sku_status' => 'used_on_page']);
        $activity = $project->activities()->where('event', 'landing_page.created')->latest()->first();
        $this->assertNotNull($activity);
        $this->assertSame([
            'landing_page_id' => $activity->payload['landing_page_id'],
            'title' => '星空投影灯',
            'shopify_product_linked' => true,
            'sku_count' => 2,
        ], $activity->payload);
    }

    public function test_website_operations_uses_product_name_automatically_for_shopify_page_title(): void
    {
        $department = Department::factory()->create(['code' => 'website_operations']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-AUTO-TITLE', 'product_name' => '自动命名产品', 'market' => 'US', 'priority' => 'medium', 'current_stage' => 'website_operations', 'status' => 'in_progress', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);
        $sku = ProductSku::create(['product_project_id' => $project->id, 'sku_code' => 'AUTO-TITLE-SKU', 'variant_name' => '默认规格', 'sku_status' => 'imported', 'created_by' => $user->id]);

        $this->actingAs($user)->post("/projects/{$project->id}/landing-pages", ['page_url' => 'https://shop.example.com/products/auto-title', 'detail_image' => UploadedFile::fake()->create('detail.jpg', 100, 'image/jpeg')])
            ->assertRedirect(route('projects.index', ['stage' => 'website_operations', 'project' => $project]));

        $this->assertDatabaseHas('landing_pages', ['product_project_id' => $project->id, 'title' => '自动命名产品']);
    }

    public function test_website_operations_displays_saved_landing_page_link_and_detail_image_in_its_work_area(): void
    {
        $department = Department::factory()->create(['code' => 'website_operations']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-PAGE-DISPLAY', 'product_name' => '回显测试产品', 'market' => 'US', 'priority' => 'medium', 'current_stage' => 'website_operations', 'status' => 'in_progress', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);
        LandingPage::create(['product_project_id' => $project->id, 'version' => 1, 'title' => $project->product_name, 'page_url' => 'https://shop.example.com/products/display-test', 'detail_image_path' => 'landing-pages/display-test.png', 'currency' => 'USD', 'status' => 'draft', 'created_by' => $user->id]);

        $this->actingAs($user)
            ->get("/projects?stage=website_operations&project={$project->id}")
            ->assertOk()
            ->assertSee('已保存的 Shopify 页面')
            ->assertSee('https://shop.example.com/products/display-test')
            ->assertSee('详情页预览')
            ->assertSee('回显测试产品详情页图片');
    }

    public function test_saved_department_work_displays_its_china_standard_time_submission_timestamp(): void
    {
        $department = Department::factory()->create(['code' => 'website_operations']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-TIMESTAMP', 'product_name' => '时间戳产品', 'market' => 'US', 'priority' => 'medium', 'current_stage' => 'website_operations', 'status' => 'in_progress', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);
        $page = LandingPage::create(['product_project_id' => $project->id, 'version' => 1, 'title' => $project->product_name, 'page_url' => 'https://shop.example.com/products/timestamp', 'currency' => 'USD', 'status' => 'draft', 'created_by' => $user->id]);
        $page->forceFill(['created_at' => '2026-09-06 20:10:00'])->save();

        $this->actingAs($user)
            ->get("/projects?stage=website_operations&project={$project->id}")
            ->assertOk()
            ->assertSee('提交于')
            ->assertSee('2026-09-06 20:10');
    }
}
