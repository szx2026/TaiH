<?php

namespace Tests\Feature\Layout;

use App\Models\Department;
use App\Models\ProductProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_member_sees_only_department_navigation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/projects')
            ->assertOk()
            ->assertSee('工作台')
            ->assertDontSee('href="http://localhost/projects"', false)
            ->assertSee('市场研究部')
            ->assertSee('网站运营部')
            ->assertSee('内容创意部')
            ->assertSee('流量增长部')
            ->assertSee('/projects?stage=market_research', false)
            ->assertSee('/projects?stage=website_operations', false)
            ->assertSee('反馈中心');
    }

    public function test_department_workspace_uses_a_project_selector(): void
    {
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create([
            'project_code' => 'PP-202609-RETURN',
            'product_name' => '返回部门项目',
            'market' => 'US',
            'priority' => 'medium',
            'current_stage' => 'market_research',
            'status' => 'in_progress',
            'owner_department_id' => $department->id,
            'owner_user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get('/projects?stage=market_research')
            ->assertOk()
            ->assertSee('产品项目')
            ->assertSee("<option value=\"{$project->id}\"", false)
            ->assertSee('返回部门项目');
    }

    public function test_workspace_back_link_returns_to_the_department_workspace_or_project_index(): void
    {
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create();
        $project = ProductProject::create([
            'project_code' => 'PP-202609-WORKSPACE',
            'product_name' => '工作台返回项目',
            'market' => 'US',
            'priority' => 'medium',
            'current_stage' => 'market_research',
            'status' => 'in_progress',
            'owner_department_id' => $department->id,
            'owner_user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get("/projects/{$project->id}/workspace?return_stage=market_research")
            ->assertRedirect("/projects?stage=market_research&project={$project->id}");

        $this->actingAs($user)
            ->get("/projects/{$project->id}/workspace")
            ->assertRedirect("/projects?stage=market_research&project={$project->id}");
    }
}
