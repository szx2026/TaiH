<?php

namespace Tests\Feature\Dashboard;

use App\Models\Department;
use App\Models\ProductProject;
use App\Models\User;
use App\Queries\DashboardQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_department_dashboard_excludes_projects_owned_by_other_stages(): void
    {
        $department = Department::factory()->create(['code' => 'website_operations']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $this->createProject($department, $user, '页面待处理产品', 'website_operations');
        $this->createProject($department, $user, '素材待处理产品', 'content_creative');

        $dashboard = app(DashboardQuery::class)->for($user);

        $this->assertTrue($dashboard['projects']->contains('product_name', '页面待处理产品'));
        $this->assertFalse($dashboard['projects']->contains('product_name', '素材待处理产品'));
    }

    private function createProject(Department $department, User $user, string $name, string $stage): void
    {
        ProductProject::create([
            'project_code' => 'PP-'.strtoupper(str_replace('产品', '', $name)),
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
