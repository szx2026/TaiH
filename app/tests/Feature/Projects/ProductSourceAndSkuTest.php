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

    public function test_website_operations_can_add_an_1688_source_without_creating_an_internal_sku(): void
    {
        $department = Department::factory()->create(['code' => 'website_operations']);
        $user = User::factory()->create(['department_id' => $department->id, 'role' => 'member']);
        $project = ProductProject::create([
            'project_code' => 'PP-202609-TEST02',
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
            ->post("/projects/{$project->id}/sources", [
                'supplier_url' => 'https://detail.1688.com/offer/1073153738003.html',
                'supplier_name' => '义乌夜灯源头工厂',
                'purchase_price' => 22.00,
                'currency' => 'CNY',
                'weight_g' => 93,
                'notes' => '确认 3 / 6 / 12 影片版本可供货。',
            ])
            ->assertRedirect(route('projects.index', ['stage' => 'website_operations', 'project' => $project]));

        $this->assertDatabaseHas('product_sources', [
            'product_project_id' => $project->id,
            'supplier_url' => 'https://detail.1688.com/offer/1073153738003.html',
            'supplier_name' => '义乌夜灯源头工厂',
            'purchase_price' => 22,
            'weight_g' => 93,
            'notes' => '确认 3 / 6 / 12 影片版本可供货。',
        ]);
        $this->assertDatabaseCount('product_skus', 0);
        $this->assertDatabaseHas('project_activities', [
            'product_project_id' => $project->id,
            'actor_id' => $user->id,
            'event' => 'supplier_source.created',
        ]);
    }
}
