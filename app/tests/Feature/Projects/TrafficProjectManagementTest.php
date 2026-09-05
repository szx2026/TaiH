<?php

namespace Tests\Feature\Projects;

use App\Models\Department;
use App\Models\ProductProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrafficProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_traffic_growth_can_archive_and_restore_a_product_project(): void
    {
        $department = Department::factory()->create(['code' => 'traffic_growth']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-ARCHIVE', 'product_name' => '可恢复项目', 'market' => 'US', 'priority' => 'medium', 'current_stage' => 'traffic_growth', 'status' => 'in_progress', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);

        $this->actingAs($user)->patch("/projects/{$project->id}/archive")->assertRedirect();
        $this->assertDatabaseHas('product_projects', ['id' => $project->id, 'status' => 'archived']);

        $this->actingAs($user)->patch("/projects/{$project->id}/restore")->assertRedirect();
        $this->assertDatabaseHas('product_projects', ['id' => $project->id, 'status' => 'in_progress']);
    }
}
