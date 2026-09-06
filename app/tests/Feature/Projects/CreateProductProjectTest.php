<?php

namespace Tests\Feature\Projects;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateProductProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_market_research_member_can_create_a_draft_product_project(): void
    {
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create([
            'department_id' => $department->id,
            'role' => 'member',
        ]);

        $response = $this->actingAs($user)->post('/projects', [
            'product_name' => '星空投影灯',
            'category' => '家居装饰',
            'market' => 'US',
            'priority' => 'high',
        ]);

        $response->assertRedirect('/projects');

        $this->assertDatabaseHas('product_projects', [
            'product_name' => '星空投影灯',
            'current_stage' => 'market_research',
            'status' => 'draft',
            'owner_user_id' => $user->id,
        ]);
    }

    public function test_product_project_creation_rejects_non_us_market(): void
    {
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id, 'role' => 'member']);

        $this->actingAs($user)
            ->from('/projects?stage=market_research')
            ->post('/projects', [
                'product_name' => '英国测试产品',
                'category' => '家居用品',
                'market' => 'UK',
                'priority' => 'high',
            ])
            ->assertRedirect('/projects?stage=market_research')
            ->assertSessionHasErrors('market');

        $this->assertDatabaseMissing('product_projects', ['product_name' => '英国测试产品']);
    }
}
