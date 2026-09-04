<?php

namespace Tests\Feature\Projects;

use App\Models\Department;
use App\Models\ProductProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_workspace_shows_stage_rail_and_recent_activity(): void
    {
        $department = Department::factory()->create(['code' => 'website_operations']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-WORKSPACE', 'product_name' => '夜灯', 'market' => 'US', 'priority' => 'high', 'current_stage' => 'website_operations', 'status' => 'in_progress', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);

        $this->actingAs($user)->get(route('projects.workspace', $project))
            ->assertOk()
            ->assertSee('阶段推进')
            ->assertSee('最近活动');
    }
}
