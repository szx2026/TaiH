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

    public function test_website_operations_can_add_an_1688_source_and_import_an_internal_sku(): void
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
                'purchase_price' => 22.00,
                'currency' => 'CNY',
                'weight_g' => 93,
                'skus' => [[
                    'sku_code' => 'NC03342609026143',
                    'variant_name' => '夜灯+3影片',
                ]],
            ])
            ->assertRedirect('/projects');

        $this->assertDatabaseHas('product_sources', [
            'product_project_id' => $project->id,
            'supplier_url' => 'https://detail.1688.com/offer/1073153738003.html',
            'purchase_price' => 22,
            'weight_g' => 93,
        ]);
        $this->assertDatabaseHas('product_skus', [
            'sku_code' => 'NC03342609026143',
            'variant_name' => '夜灯+3影片',
            'sku_status' => 'imported',
        ]);
    }
}
