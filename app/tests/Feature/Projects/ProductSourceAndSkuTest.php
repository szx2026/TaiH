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

    public function test_product_department_records_each_1688_source_with_its_matching_product_specification(): void
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
                'purchase_price' => 22.00,
                'currency' => 'CNY',
                'weight_g' => 93,
                'notes' => '确认 3 / 6 / 12 影片版本可供货。',
                'sku_code' => 'SUP-LAMP-12',
                'variant_name' => '12 影片版本',
            ])
            ->assertRedirect(route('projects.index', ['stage' => 'market_research', 'project' => $project]));

        $this->assertDatabaseHas('product_sources', [
            'product_project_id' => $project->id,
            'supplier_url' => 'https://detail.1688.com/offer/1073153738003.html',
            'supplier_name' => '义乌夜灯源头工厂',
            'purchase_price' => 22,
            'weight_g' => 93,
            'notes' => '确认 3 / 6 / 12 影片版本可供货。',
        ]);
        $this->assertDatabaseHas('product_skus', [
            'product_project_id' => $project->id,
            'sku_code' => 'SUP-LAMP-12',
            'variant_name' => '12 影片版本',
        ]);
        $this->assertDatabaseHas('project_activities', [
            'product_project_id' => $project->id,
            'actor_id' => $user->id,
            'event' => 'supplier_source.created',
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
            'currency' => 'CNY',
        ])->assertSessionHasErrors(['sku_code', 'variant_name']);
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
