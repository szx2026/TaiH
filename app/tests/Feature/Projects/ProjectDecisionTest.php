<?php

namespace Tests\Feature\Projects;

use App\Models\Department;
use App\Models\ProductProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectDecisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_market_research_can_create_a_sku_decision_for_website_operations(): void
    {
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-DECISION', 'product_name' => '星空投影灯', 'market' => 'US', 'priority' => 'high', 'current_stage' => 'website_operations', 'status' => 'in_progress', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);

        $this->actingAs($user)->post("/projects/{$project->id}/decisions", [
            'decision_type' => 'sku',
            'requested_from_stage' => 'website_operations',
            'title' => '请确认夜灯 + 12 影片 SKU 是否开通',
            'details' => 'TikTok 视频展示该规格，建议与 3/6 影片一起测试。',
        ])->assertRedirect(route('projects.workspace', ['project' => $project, 'tab' => 'operations']));

        $this->assertDatabaseHas('project_decisions', ['product_project_id' => $project->id, 'decision_type' => 'sku', 'requested_from_stage' => 'website_operations', 'status' => 'open']);
        $this->assertDatabaseHas('project_activities', ['product_project_id' => $project->id, 'event' => 'decision.created']);
    }

    public function test_website_operations_can_request_that_market_research_open_specific_skus(): void
    {
        $department = Department::factory()->create(['code' => 'website_operations']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-SKU-REQUEST', 'product_name' => '星空投影灯', 'market' => 'US', 'priority' => 'high', 'current_stage' => 'website_operations', 'status' => 'in_progress', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);

        $this->actingAs($user)->post("/projects/{$project->id}/decisions", [
            'decision_type' => 'sku',
            'requested_from_stage' => 'market_research',
            'title' => '请开通夜灯 + 12 影片 SKU',
            'details' => '落地页需要 3 / 6 / 12 影片三档规格用于价格测试。',
        ])->assertRedirect(route('projects.workspace', ['project' => $project, 'tab' => 'operations']));

        $this->assertDatabaseHas('project_decisions', [
            'product_project_id' => $project->id,
            'decision_type' => 'sku',
            'requested_from_stage' => 'market_research',
            'status' => 'open',
        ]);
    }
}
