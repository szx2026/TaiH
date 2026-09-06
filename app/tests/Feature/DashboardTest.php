<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\OptimizationFeedback;
use App\Models\ProductProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_department_member_sees_current_stage_projects_and_open_feedback(): void
    {
        $department = Department::factory()->create(['code' => 'website_operations', 'name' => '运营部']);
        $user = User::factory()->create(['department_id' => $department->id, 'role' => 'member']);
        $project = ProductProject::create([
            'project_code' => 'PP-202609-DASHBOARD01',
            'product_name' => '星空投影灯',
            'market' => 'US',
            'priority' => 'high',
            'current_stage' => 'website_operations',
            'status' => 'in_progress',
            'owner_department_id' => $department->id,
            'owner_user_id' => $user->id,
            'created_by' => $user->id,
        ]);
        OptimizationFeedback::create([
            'product_project_id' => $project->id,
            'target_stage' => 'website_operations',
            'note' => '请检查落地页规格。',
            'status' => 'open',
            'created_by' => $user->id,
        ]);
        ProductProject::create([
            'project_code' => 'PP-202609-ARCHIVED',
            'product_name' => '不应显示的归档项目',
            'market' => 'US',
            'priority' => 'high',
            'current_stage' => 'website_operations',
            'status' => 'archived',
            'owner_department_id' => $department->id,
            'owner_user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('我的工作看板')
            ->assertSee('星空投影灯')
            ->assertSee('请检查落地页规格。')
            ->assertSee('目标：运营部')
            ->assertDontSee('不应显示的归档项目');
    }
}
