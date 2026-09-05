<?php

namespace Tests\Feature\Projects;

use App\Models\Department;
use App\Models\ProductProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_workspace_redirects_to_the_requested_department_workbench(): void
    {
        [$user, $project] = $this->project('website_operations');

        $this->actingAs($user)
            ->get("/projects/{$project->id}/workspace?return_stage=market_research")
            ->assertRedirect(route('projects.index', ['stage' => 'market_research', 'project' => $project]));
    }

    public function test_legacy_workspace_uses_the_project_stage_when_return_stage_is_invalid(): void
    {
        [$user, $project] = $this->project('content_creative');

        $this->actingAs($user)
            ->get("/projects/{$project->id}/workspace?return_stage=not-a-department")
            ->assertRedirect(route('projects.index', ['stage' => 'content_creative', 'project' => $project]));
    }

    public function test_legacy_workspace_uses_the_project_stage_when_no_return_stage_is_given(): void
    {
        [$user, $project] = $this->project('traffic_growth');

        $this->actingAs($user)
            ->get("/projects/{$project->id}/workspace")
            ->assertRedirect(route('projects.index', ['stage' => 'traffic_growth', 'project' => $project]));
    }

    /** @return array{0: User, 1: ProductProject} */
    private function project(string $stage): array
    {
        $department = Department::factory()->create(['code' => $stage]);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create([
            'project_code' => 'PP-202609-LEGACY-'.$stage,
            'product_name' => '旧工作区项目',
            'market' => 'US',
            'priority' => 'high',
            'current_stage' => $stage,
            'status' => 'in_progress',
            'owner_department_id' => $department->id,
            'owner_user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        return [$user, $project];
    }
}
