<?php

namespace Tests\Feature\Projects;

use App\Models\Department;
use App\Models\ProductProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignTestTest extends TestCase
{
    use RefreshDatabase;

    public function test_traffic_growth_can_open_the_manual_campaign_test_form(): void
    {
        $department = Department::factory()->create(['code' => 'traffic_growth']);
        $user = User::factory()->create(['department_id' => $department->id, 'role' => 'member']);
        $project = ProductProject::create([
            'project_code' => 'PP-202609-CAMPAIGN02',
            'product_name' => '星空投影灯',
            'market' => 'US',
            'priority' => 'high',
            'current_stage' => 'traffic_growth',
            'status' => 'in_progress',
            'owner_department_id' => $department->id,
            'owner_user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get("/projects/{$project->id}")
            ->assertOk()
            ->assertSee('记录投放测试')
            ->assertSee('优化反馈');
    }

    public function test_traffic_growth_can_record_facebook_results_and_send_landing_page_feedback(): void
    {
        $department = Department::factory()->create(['code' => 'traffic_growth']);
        $user = User::factory()->create(['department_id' => $department->id, 'role' => 'member']);
        $project = ProductProject::create([
            'project_code' => 'PP-202609-CAMPAIGN01',
            'product_name' => '星空投影灯',
            'market' => 'US',
            'priority' => 'high',
            'current_stage' => 'traffic_growth',
            'status' => 'in_progress',
            'owner_department_id' => $department->id,
            'owner_user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->post("/projects/{$project->id}/campaign-tests", [
                'platform' => 'facebook',
                'campaign_name' => 'US 星空投影灯 · V1',
                'spend' => 80.50,
                'impressions' => 1000,
                'clicks' => 32,
                'conversions' => 1,
                'feedback_target_stage' => 'website_operations',
                'feedback_note' => 'CTR 合格但转化偏低，请检查价格和规格。',
            ])
            ->assertRedirect('/projects');

        $this->assertDatabaseHas('campaign_tests', [
            'product_project_id' => $project->id,
            'platform' => 'facebook',
            'campaign_name' => 'US 星空投影灯 · V1',
            'spend' => 80.50,
            'impressions' => 1000,
            'clicks' => 32,
            'ctr' => 3.20,
        ]);
        $this->assertDatabaseHas('optimization_feedback', [
            'product_project_id' => $project->id,
            'target_stage' => 'website_operations',
            'status' => 'open',
            'note' => 'CTR 合格但转化偏低，请检查价格和规格。',
        ]);
    }
}
