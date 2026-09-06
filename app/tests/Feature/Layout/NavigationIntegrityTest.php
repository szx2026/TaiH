<?php

namespace Tests\Feature\Layout;

use App\Models\User;
use App\Models\Department;
use App\Models\OptimizationFeedback;
use App\Models\ProductProject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_data_integration_navigation_leads_to_a_real_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/integrations')
            ->assertOk()
            ->assertSee('数据接入');
    }

    public function test_dashboard_and_feedback_center_link_directly_to_the_relevant_department_workspace(): void
    {
        $department = Department::factory()->create(['code' => 'website_operations']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-LINK', 'product_name' => '导航检查产品', 'market' => 'US', 'priority' => 'medium', 'current_stage' => 'website_operations', 'status' => 'in_progress', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);
        OptimizationFeedback::create(['product_project_id' => $project->id, 'target_stage' => 'website_operations', 'note' => '检查页面导航。', 'status' => 'open', 'created_by' => $user->id]);

        $target = "/projects?stage=website_operations&amp;project={$project->id}";

        $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee($target, false);
        $this->actingAs($user)->get('/feedback')->assertRedirect("/projects?stage=website_operations&project={$project->id}");
    }
}
