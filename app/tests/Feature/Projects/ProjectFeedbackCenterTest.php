<?php

namespace Tests\Feature\Projects;

use App\Models\Department;
use App\Models\OptimizationFeedback;
use App\Models\ProductProject;
use App\Models\ProjectDecision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectFeedbackCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_feedback_center_lists_all_open_items_for_the_signed_in_department(): void
    {
        $department = Department::factory()->create(['code' => 'website_operations']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $firstProject = ProductProject::create(['project_code' => 'PP-202609-FEEDBACK-1', 'product_name' => '页面待处理产品', 'market' => 'US', 'priority' => 'medium', 'current_stage' => 'website_operations', 'status' => 'in_progress', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);
        $secondProject = ProductProject::create(['project_code' => 'PP-202609-FEEDBACK-2', 'product_name' => '规格待确认产品', 'market' => 'US', 'priority' => 'medium', 'current_stage' => 'website_operations', 'status' => 'in_progress', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);
        $otherProject = ProductProject::create(['project_code' => 'PP-202609-FEEDBACK-3', 'product_name' => '创意待处理产品', 'market' => 'US', 'priority' => 'medium', 'current_stage' => 'content_creative', 'status' => 'in_progress', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);
        OptimizationFeedback::create(['product_project_id' => $firstProject->id, 'target_stage' => 'website_operations', 'note' => '请检查落地页规格。', 'status' => 'open', 'created_by' => $user->id]);
        OptimizationFeedback::create(['product_project_id' => $otherProject->id, 'target_stage' => 'content_creative', 'note' => '请更新视频钩子。', 'status' => 'open', 'created_by' => $user->id]);
        ProjectDecision::create(['product_project_id' => $secondProject->id, 'decision_type' => 'specification', 'requested_from_stage' => 'website_operations', 'title' => '请确认新增规格', 'status' => 'open', 'details' => [], 'created_by' => $user->id]);

        $this->actingAs($user)
            ->get('/feedback')
            ->assertOk()
            ->assertSee('全部待处理事项')
            ->assertSee('页面待处理产品')
            ->assertSee('规格待确认产品')
            ->assertDontSee('创意待处理产品')
            ->assertSee('请检查落地页规格。')
            ->assertSee('请确认新增规格')
            ->assertSee('去处理');
    }
}
