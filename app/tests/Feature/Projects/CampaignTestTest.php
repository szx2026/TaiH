<?php

namespace Tests\Feature\Projects;

use App\Models\Department;
use App\Models\CampaignTest;
use App\Models\CreativeAsset;
use App\Models\LandingPage;
use App\Models\ProjectActivity;
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

    public function test_traffic_growth_can_record_facebook_results_with_the_project_video_and_landing_page(): void
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
        $video = CreativeAsset::create([
            'product_project_id' => $project->id,
            'title' => '星空投影演示视频',
            'asset_type' => 'video',
            'source_type' => 'original',
            'status' => 'draft',
            'created_by' => $user->id,
        ]);
        $landingPage = LandingPage::create([
            'product_project_id' => $project->id,
            'version' => 1,
            'title' => '星空投影灯 Shopify 页面',
            'page_url' => 'https://shop.example.com/star-projector',
            'currency' => 'USD',
            'status' => 'draft',
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
                'creative_asset_id' => $video->id,
                'landing_page_id' => $landingPage->id,
            ])
            ->assertRedirect("/projects/{$project->id}/workspace?tab=campaigns");

        $this->assertDatabaseHas('campaign_tests', [
            'product_project_id' => $project->id,
            'platform' => 'facebook',
            'campaign_name' => 'US 星空投影灯 · V1',
            'spend' => 80.50,
            'impressions' => 1000,
            'clicks' => 32,
            'ctr' => 3.20,
            'creative_asset_id' => $video->id,
            'landing_page_id' => $landingPage->id,
        ]);
        $activity = ProjectActivity::query()
            ->where('product_project_id', $project->id)
            ->where('event', 'campaign_test.created')
            ->firstOrFail();

        $this->assertSame([
            'campaign_test_id' => CampaignTest::query()->where('product_project_id', $project->id)->value('id'),
            'campaign_name' => 'US 星空投影灯 · V1',
            'ctr' => 3.2,
            'creative_asset_id' => $video->id,
            'creative_asset_title' => '星空投影演示视频',
            'landing_page_id' => $landingPage->id,
            'landing_page_title' => '星空投影灯 Shopify 页面',
        ], $activity->payload);
    }

    public function test_traffic_growth_cannot_use_a_video_from_another_project(): void
    {
        $department = Department::factory()->create(['code' => 'traffic_growth']);
        $user = User::factory()->create(['department_id' => $department->id, 'role' => 'member']);
        $project = ProductProject::create([
            'project_code' => 'PP-202609-CAMPAIGN03', 'product_name' => '星空投影灯', 'market' => 'US',
            'priority' => 'high', 'current_stage' => 'traffic_growth', 'status' => 'in_progress',
            'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id,
        ]);
        $otherProject = ProductProject::create([
            'project_code' => 'PP-202609-CAMPAIGN04', 'product_name' => '月球灯', 'market' => 'US',
            'priority' => 'high', 'current_stage' => 'traffic_growth', 'status' => 'in_progress',
            'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id,
        ]);
        $otherVideo = CreativeAsset::create([
            'product_project_id' => $otherProject->id, 'title' => '其他项目视频', 'asset_type' => 'video',
            'source_type' => 'original', 'status' => 'draft', 'created_by' => $user->id,
        ]);
        $landingPage = LandingPage::create([
            'product_project_id' => $project->id, 'version' => 1, 'title' => '当前项目页面',
            'page_url' => 'https://shop.example.com/current', 'currency' => 'USD', 'status' => 'draft', 'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->from("/projects/{$project->id}/workspace?tab=campaigns")
            ->post("/projects/{$project->id}/campaign-tests", [
                'platform' => 'facebook', 'campaign_name' => '跨项目视频测试', 'spend' => 80.50,
                'impressions' => 1000, 'clicks' => 32, 'conversions' => 1,
                'creative_asset_id' => $otherVideo->id, 'landing_page_id' => $landingPage->id,
            ])
            ->assertSessionHasErrors('creative_asset_id');

        $this->assertDatabaseCount('campaign_tests', 0);
    }

    public function test_campaign_form_lists_only_this_projects_videos_and_landing_pages(): void
    {
        $department = Department::factory()->create(['code' => 'traffic_growth']);
        $user = User::factory()->create(['department_id' => $department->id, 'role' => 'member']);
        $project = ProductProject::create([
            'project_code' => 'PP-202609-CAMPAIGN06', 'product_name' => '星空投影灯', 'market' => 'US',
            'priority' => 'high', 'current_stage' => 'traffic_growth', 'status' => 'in_progress',
            'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id,
        ]);
        CreativeAsset::create([
            'product_project_id' => $project->id, 'title' => '当前项目视频', 'asset_type' => 'video',
            'source_type' => 'original', 'status' => 'draft', 'created_by' => $user->id,
        ]);
        CreativeAsset::create([
            'product_project_id' => $project->id, 'title' => '当前项目图片', 'asset_type' => 'image',
            'source_type' => 'original', 'status' => 'draft', 'created_by' => $user->id,
        ]);
        LandingPage::create([
            'product_project_id' => $project->id, 'version' => 1, 'title' => '当前项目 Shopify 页面',
            'page_url' => 'https://shop.example.com/current', 'currency' => 'USD', 'status' => 'draft', 'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get("/projects/{$project->id}/workspace?tab=campaigns")
            ->assertOk()
            ->assertSee('投放视频素材')
            ->assertSee('Shopify 产品/正式落地页')
            ->assertSee('当前项目视频')
            ->assertSee('当前项目 Shopify 页面')
            ->assertDontSee('当前项目图片');
    }

    public function test_traffic_growth_can_send_one_campaign_feedback_to_all_three_departments(): void
    {
        $department = Department::factory()->create(['code' => 'traffic_growth']);
        $user = User::factory()->create(['department_id' => $department->id, 'role' => 'member']);
        $project = ProductProject::create([
            'project_code' => 'PP-202609-CAMPAIGN05', 'product_name' => '星空投影灯', 'market' => 'US',
            'priority' => 'high', 'current_stage' => 'traffic_growth', 'status' => 'in_progress',
            'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id,
        ]);
        $video = CreativeAsset::create([
            'product_project_id' => $project->id, 'title' => '星空投影演示视频', 'asset_type' => 'video',
            'source_type' => 'original', 'status' => 'draft', 'created_by' => $user->id,
        ]);
        $landingPage = LandingPage::create([
            'product_project_id' => $project->id, 'version' => 1, 'title' => '星空投影灯 Shopify 页面',
            'page_url' => 'https://shop.example.com/star-projector', 'currency' => 'USD', 'status' => 'draft', 'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->post("/projects/{$project->id}/campaign-tests", [
                'platform' => 'facebook', 'campaign_name' => '三部门优化测试', 'spend' => 80.50,
                'impressions' => 1000, 'clicks' => 32, 'conversions' => 1,
                'creative_asset_id' => $video->id, 'landing_page_id' => $landingPage->id,
                'feedback_target_stages' => ['market_research', 'website_operations', 'content_creative'],
                'feedback_note' => '请分别检查产品、页面和视频表现。',
            ])
            ->assertRedirect("/projects/{$project->id}/workspace?tab=campaigns");

        $campaignId = CampaignTest::query()->where('product_project_id', $project->id)->value('id');

        $this->assertDatabaseCount('optimization_feedback', 3);
        foreach (['market_research', 'website_operations', 'content_creative'] as $stage) {
            $this->assertDatabaseHas('optimization_feedback', [
                'product_project_id' => $project->id, 'campaign_test_id' => $campaignId,
                'target_stage' => $stage, 'status' => 'open', 'note' => '请分别检查产品、页面和视频表现。',
            ]);
        }
        $this->assertSame(3, \DB::table('project_activities')
            ->where('product_project_id', $project->id)
            ->where('event', 'feedback.created')
            ->count());
    }
}
