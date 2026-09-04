<?php

namespace Tests\Feature\Projects;

use App\Models\Department;
use App\Models\OptimizationFeedback;
use App\Models\ProductProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OptimizationFeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_target_department_sees_a_feedback_response_form(): void
    {
        $department = Department::factory()->create(['code' => 'website_operations']);
        $user = User::factory()->create(['department_id' => $department->id, 'role' => 'member']);
        $project = ProductProject::create([
            'project_code' => 'PP-202609-FEEDBACK02',
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
            'note' => '请检查价格和规格。',
            'status' => 'open',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get("/projects/{$project->id}")
            ->assertOk()
            ->assertSee('处理反馈')
            ->assertSee('处理说明');
    }

    public function test_the_target_department_can_resolve_feedback_with_a_response(): void
    {
        $websiteDepartment = Department::factory()->create(['code' => 'website_operations']);
        $trafficDepartment = Department::factory()->create(['code' => 'traffic_growth']);
        $websiteUser = User::factory()->create(['department_id' => $websiteDepartment->id, 'role' => 'member']);
        $trafficUser = User::factory()->create(['department_id' => $trafficDepartment->id, 'role' => 'member']);
        $project = ProductProject::create([
            'project_code' => 'PP-202609-FEEDBACK01',
            'product_name' => '星空投影灯',
            'market' => 'US',
            'priority' => 'high',
            'current_stage' => 'website_operations',
            'status' => 'in_progress',
            'owner_department_id' => $websiteDepartment->id,
            'owner_user_id' => $websiteUser->id,
            'created_by' => $websiteUser->id,
        ]);
        $feedback = OptimizationFeedback::create([
            'product_project_id' => $project->id,
            'target_stage' => 'website_operations',
            'note' => 'CTR 合格但转化偏低，请检查价格和规格。',
            'status' => 'open',
            'created_by' => $trafficUser->id,
        ]);

        $this->actingAs($websiteUser)
            ->patch("/projects/{$project->id}/optimization-feedback/{$feedback->id}", [
                'status' => 'resolved',
                'response_note' => '已将价格调整为 34.99 美元，并增加规格说明。',
            ])
            ->assertRedirect('/projects');

        $this->assertDatabaseHas('optimization_feedback', [
            'id' => $feedback->id,
            'status' => 'resolved',
            'response_note' => '已将价格调整为 34.99 美元，并增加规格说明。',
            'resolved_by' => $websiteUser->id,
        ]);
        $this->assertDatabaseHas('project_activities', [
            'product_project_id' => $project->id,
            'actor_id' => $websiteUser->id,
            'event' => 'feedback.resolved',
        ]);
    }
}
