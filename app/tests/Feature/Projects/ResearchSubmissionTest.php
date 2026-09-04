<?php

namespace Tests\Feature\Projects;

use App\Models\Department;
use App\Models\ProductProject;
use App\Models\ResearchSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResearchSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_project_cannot_be_submitted_without_research_evidence(): void
    {
        [$user, $project] = $this->marketResearchProject();

        $this->actingAs($user)
            ->post("/projects/{$project->id}/submit", [
                'target_stage' => 'website_operations',
                'note' => '请确认货源与 SKU。',
            ])
            ->assertSessionHasErrors('research_sources');
    }

    public function test_a_project_with_research_evidence_moves_to_website_operations(): void
    {
        [$user, $project] = $this->marketResearchProject();

        ResearchSource::create([
            'product_project_id' => $project->id,
            'platform' => 'TikTok',
            'url' => 'https://www.tiktok.com/@example/video/123',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->post("/projects/{$project->id}/submit", [
                'target_stage' => 'website_operations',
                'note' => '请确认货源与 SKU。',
            ])
            ->assertRedirect('/projects');

        $this->assertDatabaseHas('product_projects', [
            'id' => $project->id,
            'current_stage' => 'website_operations',
            'status' => 'in_progress',
        ]);
        $this->assertDatabaseHas('workflow_transitions', [
            'product_project_id' => $project->id,
            'from_stage' => 'market_research',
            'to_stage' => 'website_operations',
            'action' => 'submit',
            'operator_id' => $user->id,
        ]);
        $this->assertDatabaseHas('project_activities', [
            'product_project_id' => $project->id,
            'actor_id' => $user->id,
            'event' => 'stage.advanced',
        ]);
    }

    /** @return array{User, ProductProject} */
    private function marketResearchProject(): array
    {
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id, 'role' => 'member']);
        $project = ProductProject::create([
            'project_code' => 'PP-202609-TEST01',
            'product_name' => '星空投影灯',
            'market' => 'US',
            'priority' => 'high',
            'current_stage' => 'market_research',
            'status' => 'draft',
            'owner_department_id' => $department->id,
            'owner_user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        return [$user, $project];
    }
}
