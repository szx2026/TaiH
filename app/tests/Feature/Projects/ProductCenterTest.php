<?php

namespace Tests\Feature\Projects;

use App\Models\Department;
use App\Models\ProductProject;
use App\Models\ProductSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_center_filters_by_stage_and_search_term(): void
    {
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $this->project($department, $user, 'PP-202609-LAMP', '星空投影灯', 'market_research');
        $this->project($department, $user, 'PP-202609-BAG', '旅行收纳包', 'website_operations');

        $this->actingAs($user)->get('/projects?stage=market_research&search=投影')
            ->assertOk()
            ->assertSee('星空投影灯')
            ->assertDontSee('旅行收纳包');
    }

    public function test_department_workspaces_do_not_filter_shared_projects_by_department(): void
    {
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $this->project($department, $user, 'PP-202609-SHARED', '跨部门协作项目', 'website_operations');

        $this->actingAs($user)->get('/projects?stage=market_research')
            ->assertOk()
            ->assertSee('跨部门协作项目')
            ->assertDontSee('全部部门')
            ->assertSee('<input type="hidden" name="stage" value="market_research">', false);
    }

    public function test_product_creation_tags_for_category_and_priority_filter_shared_projects(): void
    {
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id]);
        ProductProject::create(['project_code' => 'PP-202609-PET', 'product_name' => '宠物梳毛器', 'category' => '宠物用品', 'market' => 'US', 'priority' => 'high', 'current_stage' => 'market_research', 'status' => 'in_progress', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);
        ProductProject::create(['project_code' => 'PP-202609-HOME', 'product_name' => '衣物收纳盒', 'category' => '家居用品', 'market' => 'US', 'priority' => 'low', 'current_stage' => 'market_research', 'status' => 'in_progress', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);

        $this->actingAs($user)->get('/projects?stage=market_research&category=宠物用品&priority=high')
            ->assertOk()
            ->assertSee('宠物梳毛器')
            ->assertDontSee('衣物收纳盒')
            ->assertSee('产品类目')
            ->assertDontSee('全部市场');
    }

    public function test_website_operations_link_opens_a_department_workspace_not_a_generic_product_pool(): void
    {
        $department = Department::factory()->create(['code' => 'website_operations']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $this->project($department, $user, 'PP-202609-SHOPIFY', 'Shopify 待上架产品', 'website_operations');

        $this->actingAs($user)->get('/projects?stage=website_operations')
            ->assertOk()
            ->assertSee('运营部工作台')
            ->assertSee('1688 货源、Shopify 上架与产品页')
            ->assertSee('产品项目')
            ->assertSee('Shopify 待上架产品')
            ->assertDontSee('手动创建产品项目');
    }

    public function test_market_research_workspace_displays_selected_product_and_research_work_inline(): void
    {
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-INLINE', 'product_name' => '内嵌选品项目', 'market' => 'US', 'priority' => 'medium', 'current_stage' => 'market_research', 'status' => 'in_progress', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);

        $this->actingAs($user)->get("/projects?stage=market_research&project={$project->id}")
            ->assertOk()
            ->assertSee('内嵌选品项目')
            ->assertSee('产品部重点工作')
            ->assertSee('选品、产品规格与货源产品信息')
            ->assertSee('关联协作摘要');
    }

    public function test_selected_project_displays_a_compact_stage_progress_indicator(): void
    {
        $department = Department::factory()->create(['code' => 'website_operations']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = $this->project($department, $user, 'PP-202609-PROGRESS', '进程展示项目', 'website_operations');

        $this->actingAs($user)->get("/projects?stage=website_operations&project={$project->id}")
            ->assertOk()
            ->assertSee('项目进程')
            ->assertSee('data-project-stage-progress', false)
            ->assertSee('当前环节：运营部')
            ->assertSeeInOrder(['产品部', '运营部', '创意部', '流量部']);
    }

    public function test_each_department_workspace_prioritizes_its_own_product_work(): void
    {
        $department = Department::factory()->create(['code' => 'website_operations']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = $this->project($department, $user, 'PP-202609-DEPT-FOCUS', '部门重点项目', 'website_operations');

        $this->actingAs($user)->get("/projects?stage=website_operations&project={$project->id}")->assertOk()->assertSee('运营部重点工作')->assertSee('最终产品规格与 Shopify 产品');
        $this->actingAs($user)->get("/projects?stage=content_creative&project={$project->id}")->assertOk()->assertSee('创意部重点工作')->assertSee('素材清单');
        $this->actingAs($user)->get("/projects?stage=traffic_growth&project={$project->id}")->assertOk()->assertSee('流量部重点工作')->assertSee('投放测试与反馈');
    }

    public function test_department_workbenches_show_active_products_in_parallel(): void
    {
        $department = Department::factory()->create(['code' => 'content_creative']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = $this->project($department, $user, 'PP-202609-PARALLEL', '并联协作项目', 'market_research');

        $this->actingAs($user)->get('/projects?stage=content_creative')
            ->assertOk()
            ->assertSee('并联协作项目');
        $this->actingAs($user)->get("/projects?stage=content_creative&project={$project->id}")
            ->assertOk()
            ->assertSee('创意部重点工作');
    }

    public function test_creative_department_can_review_complete_product_information_after_its_own_work(): void
    {
        $productDepartment = Department::factory()->create(['code' => 'market_research']);
        $creativeDepartment = Department::factory()->create(['code' => 'content_creative']);
        $productUser = User::factory()->create(['department_id' => $productDepartment->id]);
        $creativeUser = User::factory()->create(['department_id' => $creativeDepartment->id]);
        $project = $this->project($productDepartment, $productUser, 'PP-202609-SHARED-INFO', '共享资料项目', 'market_research');
        ProductSource::create([
            'product_project_id' => $project->id,
            'supplier_url' => 'https://detail.1688.com/offer/shared-product.html',
            'supplier_name' => '产品部录入的 1688 货源',
            'currency' => 'CNY',
            'created_by' => $productUser->id,
        ]);

        $this->actingAs($creativeUser)
            ->get("/projects?stage=content_creative&project={$project->id}")
            ->assertOk()
            ->assertSeeInOrder(['创意部重点工作', '项目共享资料'])
            ->assertSee('产品部资料')
            ->assertSee('产品部录入的 1688 货源');
    }

    public function test_every_department_includes_shared_creative_downloads_and_facebook_metrics_inside_project_shared_materials(): void
    {
        $department = Department::factory()->create(['code' => 'website_operations']);
        $user = User::factory()->create(['department_id' => $department->id, 'role' => 'administrator']);
        $project = $this->project($department, $user, 'PP-202609-SHARED-ALL', '全员共享资料项目', 'website_operations');

        foreach (['market_research', 'website_operations', 'content_creative', 'traffic_growth'] as $stage) {
            $content = $this->actingAs($user)
                ->get("/projects?stage={$stage}&project={$project->id}")
                ->assertOk()
                ->getContent();
            $this->assertStringContainsString('sharedMaterialsGrid.append', $content);
            $this->assertStringContainsString('standaloneSharedMaterials?.remove', $content);
            $this->assertStringContainsString('sharedCampaignFeedbackPanel?.remove', $content);
            $this->assertStringContainsString('trafficSharedMaterials', $content);
            $this->assertStringContainsString('sharedCampaignFeedbackPanel', $content);
            $this->assertStringContainsString('creativeDownloadActions', $content);
            $this->assertStringContainsString('hasPendingFeedback', $content);
            $this->assertStringContainsString('pendingFeedbackPanel?.remove', $content);
            $this->assertStringContainsString('sharedDepartmentCardPalette', $content);
        }
    }

    public function test_department_workspace_keeps_the_current_departments_edit_form_inline(): void
    {
        $department = Department::factory()->create(['code' => 'traffic_growth']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = $this->project($department, $user, 'PP-202609-EDIT-INLINE', '可编辑项目', 'traffic_growth');

        $this->actingAs($user)->get("/projects?stage=traffic_growth&project={$project->id}")
            ->assertOk()
            ->assertSee("/projects/{$project->id}/campaign-tests", false)
            ->assertSee('保存投放测试');
    }

    public function test_traffic_growth_workbench_offers_a_screenshot_paste_area(): void
    {
        $department = Department::factory()->create(['code' => 'traffic_growth']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = $this->project($department, $user, 'PP-202609-PASTE', '截图粘贴项目', 'traffic_growth');

        $this->actingAs($user)->get("/projects?stage=traffic_growth&project={$project->id}")
            ->assertOk()
            ->assertSee('点击此处后按 Ctrl+V 粘贴截图')
            ->assertSee('data-paste-upload', false);
    }

    public function test_all_department_workspaces_expose_their_own_manual_entry_actions_inline(): void
    {
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = $this->project($department, $user, 'PP-202609-ALL-INLINE', '全链路编辑项目', 'market_research');

        $this->actingAs($user)->get("/projects?stage=market_research&project={$project->id}")->assertOk()->assertSee("/projects/{$project->id}/research-sources", false)->assertSee("/projects/{$project->id}/skus", false)->assertSee("/projects/{$project->id}/sources", false);
        $websiteDepartment = Department::factory()->create(['code' => 'website_operations']);
        $websiteUser = User::factory()->create(['department_id' => $websiteDepartment->id]);
        $creativeDepartment = Department::factory()->create(['code' => 'content_creative']);
        $creativeUser = User::factory()->create(['department_id' => $creativeDepartment->id]);
        $this->actingAs($websiteUser)->get("/projects?stage=website_operations&project={$project->id}")->assertOk()->assertDontSee("/projects/{$project->id}/sources", false)->assertSee("/projects/{$project->id}/landing-pages", false);
        $this->actingAs($creativeUser)->get("/projects?stage=content_creative&project={$project->id}")->assertOk()->assertSee("/projects/{$project->id}/creative-assets", false);
    }

    private function project(Department $department, User $user, string $code, string $name, string $stage): ProductProject
    {
        return ProductProject::create([
            'project_code' => $code,
            'product_name' => $name,
            'market' => 'US',
            'priority' => 'medium',
            'current_stage' => $stage,
            'status' => 'in_progress',
            'owner_department_id' => $department->id,
            'owner_user_id' => $user->id,
            'created_by' => $user->id,
        ]);
    }
}
