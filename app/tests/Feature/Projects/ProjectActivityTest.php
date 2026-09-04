<?php

namespace Tests\Feature\Projects;

use App\Actions\Activity\RecordProjectActivity;
use App\Models\Department;
use App\Models\ProductProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_recording_a_project_event_creates_an_immutable_activity_item(): void
    {
        $department = Department::factory()->create(['code' => 'website_operations']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create([
            'project_code' => 'PP-202609-ACTIVITY',
            'product_name' => '星空投影灯',
            'market' => 'US',
            'priority' => 'high',
            'current_stage' => 'website_operations',
            'status' => 'in_progress',
            'owner_department_id' => $department->id,
            'owner_user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        app(RecordProjectActivity::class)->handle($project, $user, 'sku.created', [
            'sku_code' => 'NC03342609026143',
        ]);

        $this->assertDatabaseHas('project_activities', [
            'product_project_id' => $project->id,
            'actor_id' => $user->id,
            'event' => 'sku.created',
        ]);
    }
}
