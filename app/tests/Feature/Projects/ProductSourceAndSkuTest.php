<?php

namespace Tests\Feature\Projects;

use App\Models\Department;
use App\Models\ProductProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSourceAndSkuTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_department_records_an_1688_source_and_initial_specification_before_assigning_an_internal_sku(): void
    {
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id, 'role' => 'member']);
        $project = ProductProject::create([
            'project_code' => 'PP-202609-TEST02',
            'product_name' => '星空投影灯',
            'market' => 'US',
            'priority' => 'high',
            'current_stage' => 'market_research',
            'status' => 'in_progress',
            'owner_department_id' => $department->id,
            'owner_user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->post("/projects/{$project->id}/sources", [
                'supplier_url' => 'https://detail.1688.com/offer/1073153738003.html',
                'supplier_name' => '义乌夜灯源头工厂',
                'product_name' => '星空投影灯',
                'currency' => 'CNY',
                'notes' => '确认 3 / 6 / 12 影片版本可供货。',
                'specifications' => [[
                    'sku_code' => 'SUP-LAMP-12',
                    'variant_name' => '12 影片版本',
                    'purchase_price' => 22.00,
                    'weight_g' => 93,
                ]],
            ])
            ->assertRedirect(route('projects.index', ['stage' => 'market_research', 'project' => $project]));

        $this->assertDatabaseHas('product_sources', [
            'product_project_id' => $project->id,
            'supplier_url' => 'https://detail.1688.com/offer/1073153738003.html',
            'supplier_name' => '义乌夜灯源头工厂',
            'product_name' => '星空投影灯',
            'notes' => '确认 3 / 6 / 12 影片版本可供货。',
        ]);
        $this->assertDatabaseHas('product_skus', [
            'product_project_id' => $project->id,
            'sku_code' => 'SUP-LAMP-12',
            'variant_name' => '12 影片版本',
            'purchase_price' => 22,
            'weight_g' => 93,
            'sku_status' => 'internal_confirmed',
        ]);
        $this->assertDatabaseHas('project_activities', [
            'product_project_id' => $project->id,
            'actor_id' => $user->id,
            'event' => 'supplier_source.created',
        ]);
        $this->assertDatabaseHas('project_decisions', [
            'product_project_id' => $project->id,
            'decision_type' => 'specification',
            'requested_from_stage' => 'website_operations',
            'status' => 'open',
        ]);
    }

    public function test_product_source_requires_a_matching_product_specification(): void
    {
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id, 'role' => 'member']);
        $project = ProductProject::create([
            'project_code' => 'PP-202609-TEST04', 'product_name' => '规格校验产品', 'market' => 'US',
            'priority' => 'initial_screening', 'current_stage' => 'market_research', 'status' => 'draft',
            'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id,
        ]);

        $this->actingAs($user)->post("/projects/{$project->id}/sources", [
            'supplier_url' => 'https://detail.1688.com/offer/1073153738003.html',
            'supplier_name' => '测试供应商',
            'product_name' => '测试产品',
            'notes' => '测试货源。',
            'currency' => 'CNY',
        ])->assertSessionHasErrors(['specifications']);
    }

    public function test_multiple_product_specifications_can_share_one_supplier_source(): void
    {
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-TEST05', 'product_name' => '共享货源产品', 'market' => 'US', 'priority' => 'market_new', 'current_stage' => 'market_research', 'status' => 'draft', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);
        $source = ['supplier_url' => 'https://detail.1688.com/offer/shared-source.html', 'supplier_name' => '共享货源工厂', 'product_name' => '共享货源产品', 'currency' => 'CNY', 'notes' => '同一货源下有多个规格。'];

        $this->actingAs($user)->post("/projects/{$project->id}/sources", [...$source, 'specifications' => [
            ['sku_code' => 'SKU-US-001', 'variant_name' => '单件', 'purchase_price' => 10, 'weight_g' => 100],
            ['sku_code' => 'SKU-US-002', 'variant_name' => '两件套', 'purchase_price' => 18, 'weight_g' => 180],
        ]])->assertRedirect();

        $this->assertDatabaseCount('product_sources', 1);
        $this->assertDatabaseHas('product_skus', ['sku_code' => 'SKU-US-002', 'variant_name' => '两件套', 'purchase_price' => 18, 'weight_g' => 180]);
    }

    public function test_product_department_can_add_alternative_supplier_names_and_source_urls_for_the_same_product(): void
    {
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-ALTERNATIVE-SOURCES', 'product_name' => '多商家产品', 'market' => 'US', 'priority' => 'medium', 'current_stage' => 'market_research', 'status' => 'draft', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);

        $this->actingAs($user)->post("/projects/{$project->id}/sources", [
            'supplier_url' => 'https://detail.1688.com/offer/main-source.html',
            'supplier_name' => '主供应商',
            'product_name' => '多商家产品',
            'currency' => 'CNY',
            'notes' => '用于比较同款供应商。',
            'specifications' => [['sku_code' => 'MULTI-SOURCE-01', 'variant_name' => '单件', 'purchase_price' => 12, 'weight_g' => 100]],
            'alternative_sources' => [
                ['supplier_name' => '备选供应商 A', 'supplier_url' => 'https://detail.1688.com/offer/alternative-a.html'],
                ['supplier_name' => '备选供应商 B', 'supplier_url' => 'https://detail.1688.com/offer/alternative-b.html'],
            ],
        ])->assertRedirect();

        $this->assertDatabaseCount('product_sources', 3);
        $this->assertDatabaseHas('product_sources', ['product_project_id' => $project->id, 'supplier_name' => '备选供应商 A', 'supplier_url' => 'https://detail.1688.com/offer/alternative-a.html']);
        $this->assertDatabaseHas('product_sources', ['product_project_id' => $project->id, 'supplier_name' => '备选供应商 B', 'supplier_url' => 'https://detail.1688.com/offer/alternative-b.html']);
    }

    public function test_operations_cannot_add_an_1688_source(): void
    {
        $productDepartment = Department::factory()->create(['code' => 'market_research']);
        $operationsDepartment = Department::factory()->create(['code' => 'website_operations']);
        $productUser = User::factory()->create(['department_id' => $productDepartment->id, 'role' => 'member']);
        $operationsUser = User::factory()->create(['department_id' => $operationsDepartment->id, 'role' => 'member']);
        $project = ProductProject::create([
            'project_code' => 'PP-202609-TEST03',
            'product_name' => '便携榨汁杯',
            'market' => 'US',
            'priority' => 'medium',
            'current_stage' => 'market_research',
            'status' => 'in_progress',
            'owner_department_id' => $productDepartment->id,
            'owner_user_id' => $productUser->id,
            'created_by' => $productUser->id,
        ]);

        $this->actingAs($operationsUser)
            ->post("/projects/{$project->id}/sources", [
                'supplier_url' => 'https://detail.1688.com/offer/1073153738003.html',
                'currency' => 'CNY',
            ])
            ->assertForbidden();
    }
}
