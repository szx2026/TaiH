<?php

namespace Tests\Feature\Projects;

use App\Models\Department;
use App\Models\LandingPage;
use App\Models\ProductProject;
use App\Models\ProductSku;
use App\Models\ProductSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StageHandoffTest extends TestCase
{
    use RefreshDatabase;

    public function test_website_operations_can_handoff_only_after_source_sku_and_landing_page_exist(): void
    {
        $department = Department::factory()->create(['code' => 'website_operations']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-HANDOFF', 'product_name' => '星空投影灯', 'market' => 'US', 'priority' => 'high', 'current_stage' => 'website_operations', 'status' => 'in_progress', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);

        $this->actingAs($user)->post("/projects/{$project->id}/submit", ['target_stage' => 'content_creative'])->assertSessionHasErrors('handoff');

        $source = ProductSource::create(['product_project_id' => $project->id, 'supplier_url' => 'https://detail.1688.com/offer/123.html', 'currency' => 'CNY', 'created_by' => $user->id]);
        $sku = ProductSku::create(['product_project_id' => $project->id, 'product_source_id' => $source->id, 'sku_code' => 'NC03342609026143', 'variant_name' => '夜灯+3影片', 'sku_status' => 'used_on_page', 'created_by' => $user->id]);
        $page = LandingPage::create(['product_project_id' => $project->id, 'version' => 1, 'title' => '夜灯页面 V1', 'page_url' => 'https://shop.example.com/night-light', 'currency' => 'USD', 'status' => 'draft', 'created_by' => $user->id]);
        $page->skus()->sync([$sku->id]);

        $this->actingAs($user)->post("/projects/{$project->id}/submit", ['target_stage' => 'content_creative', 'note' => '请根据页面与 SKU 制作素材。'])->assertRedirect('/projects');

        $this->assertDatabaseHas('product_projects', ['id' => $project->id, 'current_stage' => 'content_creative']);
        $this->assertDatabaseHas('project_activities', ['product_project_id' => $project->id, 'event' => 'stage.advanced']);
    }

    public function test_current_stage_owner_sees_the_next_stage_handoff_action(): void
    {
        $department = Department::factory()->create(['code' => 'website_operations']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-HANDOFF-UI', 'product_name' => '星空投影灯', 'market' => 'US', 'priority' => 'high', 'current_stage' => 'website_operations', 'status' => 'in_progress', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);

        $this->actingAs($user)->get("/projects?stage=website_operations&project={$project->id}")
            ->assertOk()
            ->assertSee('网站运营部重点工作');
    }
}
