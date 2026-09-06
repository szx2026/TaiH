<?php

namespace Tests\Feature\Projects;

use App\Models\Department;
use App\Models\ProductProject;
use App\Models\ProductSource;
use App\Models\ProductSku;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectDecisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_market_research_can_create_a_sku_decision_for_website_operations(): void
    {
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-DECISION', 'product_name' => '星空投影灯', 'market' => 'US', 'priority' => 'high', 'current_stage' => 'website_operations', 'status' => 'in_progress', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);

        $this->actingAs($user)->post("/projects/{$project->id}/decisions", [
            'decision_type' => 'sku',
            'requested_from_stage' => 'website_operations',
            'title' => '请确认夜灯 + 12 影片 SKU 是否开通',
            'details' => 'TikTok 视频展示该规格，建议与 3/6 影片一起测试。',
        ])->assertRedirect(route('projects.index', ['stage' => 'market_research', 'project' => $project]));

        $this->assertDatabaseHas('project_decisions', ['product_project_id' => $project->id, 'decision_type' => 'sku', 'requested_from_stage' => 'website_operations', 'status' => 'open']);
        $this->assertDatabaseHas('project_activities', ['product_project_id' => $project->id, 'event' => 'decision.created']);
    }

    public function test_website_operations_cannot_initiate_a_product_specification_request(): void
    {
        $department = Department::factory()->create(['code' => 'website_operations']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-SKU-REQUEST', 'product_name' => '星空投影灯', 'market' => 'US', 'priority' => 'high', 'current_stage' => 'website_operations', 'status' => 'in_progress', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);

        $this->actingAs($user)->post("/projects/{$project->id}/decisions", [
            'decision_type' => 'sku',
            'requested_from_stage' => 'market_research',
            'title' => '请开通夜灯 + 12 影片 SKU',
            'details' => '落地页需要 3 / 6 / 12 影片三档规格用于价格测试。',
        ])->assertForbidden();
    }

    public function test_website_operations_can_reply_to_a_product_departments_sku_question(): void
    {
        $productDepartment = Department::factory()->create(['code' => 'market_research']);
        $operationsDepartment = Department::factory()->create(['code' => 'website_operations']);
        $productUser = User::factory()->create(['department_id' => $productDepartment->id]);
        $operationsUser = User::factory()->create(['department_id' => $operationsDepartment->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-SKU-REPLY', 'product_name' => 'SKU 回复测试产品', 'market' => 'US', 'priority' => 'high', 'current_stage' => 'website_operations', 'status' => 'in_progress', 'owner_department_id' => $productDepartment->id, 'owner_user_id' => $productUser->id, 'created_by' => $productUser->id]);

        $this->actingAs($productUser)->post("/projects/{$project->id}/decisions", [
            'decision_type' => 'sku', 'requested_from_stage' => 'website_operations',
            'title' => '请确认详情页需要哪些 SKU', 'details' => '请说明规格组合。',
        ]);
        $decisionId = $project->decisions()->value('id');

        $this->actingAs($operationsUser)
            ->patch("/projects/{$project->id}/decisions/{$decisionId}", ['response_note' => '详情页需要单件、两件套和四件套。'])
            ->assertRedirect(route('projects.index', ['stage' => 'website_operations', 'project' => $project]));

        $this->assertDatabaseHas('project_decisions', ['id' => $decisionId, 'status' => 'resolved', 'response_note' => '详情页需要单件、两件套和四件套。']);
        $this->actingAs($productUser)->get("/projects?stage=market_research&project={$project->id}")
            ->assertOk()
            ->assertSee('运营部回复')
            ->assertSee('详情页需要单件、两件套和四件套。');
    }

    public function test_operations_receives_a_confirmation_request_for_existing_product_specifications(): void
    {
        $productDepartment = Department::factory()->create(['code' => 'market_research']);
        $operationsDepartment = Department::factory()->create(['code' => 'website_operations']);
        $productUser = User::factory()->create(['department_id' => $productDepartment->id]);
        $operationsUser = User::factory()->create(['department_id' => $operationsDepartment->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-SPEC-RECEIVE', 'product_name' => '规格交接产品', 'market' => 'US', 'priority' => 'market_new', 'current_stage' => 'market_research', 'status' => 'draft', 'owner_department_id' => $productDepartment->id, 'owner_user_id' => $productUser->id, 'created_by' => $productUser->id]);
        $source = ProductSource::create(['product_project_id' => $project->id, 'supplier_url' => 'https://detail.1688.com/offer/spec-receive.html', 'supplier_name' => '测试工厂', 'product_name' => '规格交接产品', 'currency' => 'CNY', 'notes' => '初步货源信息。', 'created_by' => $productUser->id]);
        ProductSku::create(['product_project_id' => $project->id, 'product_source_id' => $source->id, 'sku_code' => 'SPEC-001', 'variant_name' => '单件装', 'purchase_price' => 19, 'weight_g' => 320, 'sku_status' => 'internal_confirmed', 'created_by' => $productUser->id]);

        $this->actingAs($operationsUser)
            ->get("/projects?stage=website_operations&project={$project->id}")
            ->assertOk()
            ->assertSee('待确认的初步产品规格')
            ->assertSee('SPEC-001');

        $this->assertDatabaseHas('project_decisions', ['product_project_id' => $project->id, 'decision_type' => 'specification', 'requested_from_stage' => 'website_operations', 'status' => 'open']);
    }

    public function test_operations_can_request_new_specifications_without_changing_product_departments_internal_sku(): void
    {
        $productDepartment = Department::factory()->create(['code' => 'market_research']);
        $operationsDepartment = Department::factory()->create(['code' => 'website_operations']);
        $productUser = User::factory()->create(['department_id' => $productDepartment->id]);
        $operationsUser = User::factory()->create(['department_id' => $operationsDepartment->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-SPEC-FINAL', 'product_name' => '最终规格产品', 'market' => 'US', 'priority' => 'market_new', 'current_stage' => 'market_research', 'status' => 'draft', 'owner_department_id' => $productDepartment->id, 'owner_user_id' => $productUser->id, 'created_by' => $productUser->id]);
        $source = ProductSource::create(['product_project_id' => $project->id, 'supplier_url' => 'https://detail.1688.com/offer/spec-final.html', 'supplier_name' => '测试工厂', 'product_name' => '最终规格产品', 'currency' => 'CNY', 'notes' => '初步货源信息。', 'created_by' => $productUser->id]);
        $sku = ProductSku::create(['product_project_id' => $project->id, 'product_source_id' => $source->id, 'sku_code' => 'INTERNAL-001', 'variant_name' => '单件装', 'sku_status' => 'internal_confirmed', 'created_by' => $productUser->id]);
        $decision = $project->decisions()->create(['decision_type' => 'specification', 'requested_from_stage' => 'website_operations', 'title' => '确认初步规格', 'status' => 'open', 'details' => ['initial_specifications' => [['sku_id' => $sku->id, 'sku_code' => $sku->sku_code, 'variant_name' => $sku->variant_name]]], 'created_by' => $productUser->id]);

        $this->actingAs($operationsUser)
            ->patch("/projects/{$project->id}/decisions/{$decision->id}", [
                'requested_specifications' => ['两件套', '礼盒装'],
            ])
            ->assertRedirect(route('projects.index', ['stage' => 'website_operations', 'project' => $project]));

        $this->assertDatabaseHas('product_skus', ['id' => $sku->id, 'sku_code' => 'INTERNAL-001', 'variant_name' => '单件装']);
        $this->assertDatabaseHas('project_decisions', ['id' => $decision->id, 'status' => 'resolved', 'response_note' => '运营部新增产品规格：两件套；礼盒装']);

        $this->actingAs($productUser)
            ->get("/projects?stage=market_research&project={$project->id}")
            ->assertOk()
            ->assertSee('当前已录入的产品规格')
            ->assertSee('运营部新增规格待开发')
            ->assertSee('两件套')
            ->assertSee('礼盒装');
    }

    public function test_operations_can_confirm_product_departments_initial_specifications_without_adding_new_requirements(): void
    {
        $productDepartment = Department::factory()->create(['code' => 'market_research']);
        $operationsDepartment = Department::factory()->create(['code' => 'website_operations']);
        $productUser = User::factory()->create(['department_id' => $productDepartment->id]);
        $operationsUser = User::factory()->create(['department_id' => $operationsDepartment->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-SPEC-ADOPT', 'product_name' => '确认规格产品', 'market' => 'US', 'priority' => 'market_new', 'current_stage' => 'market_research', 'status' => 'draft', 'owner_department_id' => $productDepartment->id, 'owner_user_id' => $productUser->id, 'created_by' => $productUser->id]);
        $source = ProductSource::create(['product_project_id' => $project->id, 'supplier_url' => 'https://detail.1688.com/offer/spec-adopt.html', 'supplier_name' => '测试工厂', 'product_name' => '确认规格产品', 'currency' => 'CNY', 'notes' => '初步货源信息。', 'created_by' => $productUser->id]);
        $sku = ProductSku::create(['product_project_id' => $project->id, 'product_source_id' => $source->id, 'sku_code' => 'INTERNAL-ADOPT-001', 'variant_name' => '单件装', 'sku_status' => 'internal_confirmed', 'created_by' => $productUser->id]);
        $decision = $project->decisions()->create(['decision_type' => 'specification', 'requested_from_stage' => 'website_operations', 'title' => '确认初步规格', 'status' => 'open', 'details' => ['initial_specifications' => [['sku_id' => $sku->id, 'sku_code' => $sku->sku_code, 'variant_name' => $sku->variant_name]]], 'created_by' => $productUser->id]);

        $this->actingAs($operationsUser)
            ->patch("/projects/{$project->id}/decisions/{$decision->id}", [
                'specification_action' => 'adopt',
            ])
            ->assertRedirect(route('projects.index', ['stage' => 'website_operations', 'project' => $project]));

        $this->assertDatabaseHas('product_skus', ['id' => $sku->id, 'sku_code' => 'INTERNAL-ADOPT-001', 'variant_name' => '单件装']);
        $this->assertDatabaseHas('project_decisions', ['id' => $decision->id, 'status' => 'resolved', 'response_note' => '运营部确认采用产品部初步规格。']);
    }
}
