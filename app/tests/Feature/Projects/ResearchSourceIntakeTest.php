<?php

namespace Tests\Feature\Projects;

use App\Models\Department;
use App\Models\ProductProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResearchSourceIntakeTest extends TestCase
{
    use RefreshDatabase;

    public function test_market_research_can_add_a_tiktok_evidence_link_to_a_product(): void
    {
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-RESEARCH', 'product_name' => '星空投影灯', 'market' => 'US', 'priority' => 'high', 'current_stage' => 'market_research', 'status' => 'draft', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);

        $this->actingAs($user)->post("/projects/{$project->id}/research-sources", [
            'platform' => 'tiktok',
            'url' => 'https://www.tiktok.com/@example/video/123',
            'evidence_note' => '多个卖家持续投放，视频展示效果强。',
        ])->assertRedirect(route('projects.index', ['stage' => 'market_research', 'project' => $project]));

        $this->assertDatabaseHas('research_sources', ['product_project_id' => $project->id, 'platform' => 'tiktok']);
        $this->assertDatabaseHas('project_activities', ['product_project_id' => $project->id, 'event' => 'research_source.created']);
    }

    public function test_market_research_updates_the_current_product_selection_information_instead_of_creating_another_record(): void
    {
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-RESEARCH-MULTI', 'product_name' => '果蔬清洗杯', 'market' => 'US', 'priority' => 'high', 'current_stage' => 'market_research', 'status' => 'draft', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);

        $this->actingAs($user)->post("/projects/{$project->id}/research-sources", [
            'platform' => 'independent_store',
            'url' => 'https://example-store.com/products/cup',
            'evidence_note' => '初次记录的选品判断。',
        ])->assertRedirect();
        $this->actingAs($user)->post("/projects/{$project->id}/research-sources", [
            'platform' => 'tiktok',
            'custom_source_name' => 'Etsy',
            'url' => 'https://etsy.com/listing/example',
            'evidence_note' => '修改后的选品判断。',
        ])->assertRedirect();

        $this->assertDatabaseCount('research_sources', 1);
        $this->assertDatabaseHas('research_sources', ['product_project_id' => $project->id, 'platform' => 'tiktok', 'custom_source_name' => 'Etsy', 'evidence_note' => '修改后的选品判断。']);
        $this->assertDatabaseHas('project_activities', ['product_project_id' => $project->id, 'event' => 'research_source.updated']);
    }
}
