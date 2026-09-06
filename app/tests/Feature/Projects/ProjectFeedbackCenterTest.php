<?php

namespace Tests\Feature\Projects;

use App\Models\Department;
use App\Models\OptimizationFeedback;
use App\Models\ProductProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectFeedbackCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_target_department_only_sees_its_open_feedback_in_feedback_center(): void
    {
        $department = Department::factory()->create(['code' => 'website_operations']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-FEEDBACK', 'product_name' => '测试产品', 'market' => 'US', 'priority' => 'medium', 'current_stage' => 'website_operations', 'status' => 'in_progress', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);
        OptimizationFeedback::create(['product_project_id' => $project->id, 'target_stage' => 'website_operations', 'note' => '请检查落地页规格。', 'status' => 'open', 'created_by' => $user->id]);
        OptimizationFeedback::create(['product_project_id' => $project->id, 'target_stage' => 'content_creative', 'note' => '请更新视频钩子。', 'status' => 'open', 'created_by' => $user->id]);

        $this->actingAs($user)->get('/feedback')->assertOk()->assertSee('请检查落地页规格。')->assertDontSee('请更新视频钩子。')->assertSee('>1<', false);
    }
}
