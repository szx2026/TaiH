<?php

namespace Tests\Feature\Projects;

use App\Models\Department;
use App\Models\ProductProject;
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

    private function project(Department $department, User $user, string $code, string $name, string $stage): void
    {
        ProductProject::create([
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
