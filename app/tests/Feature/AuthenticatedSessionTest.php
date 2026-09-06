<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticatedSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_department_members_enter_their_own_workspace_after_logging_in(): void
    {
        foreach ([
            'market_research' => 'products@example.com',
            'website_operations' => 'operations@example.com',
            'content_creative' => 'creative@example.com',
            'traffic_growth' => 'traffic@example.com',
        ] as $departmentCode => $email) {
            $department = Department::factory()->create(['code' => $departmentCode]);
            User::factory()->create(['department_id' => $department->id, 'email' => $email, 'password' => 'password', 'role' => 'member']);

            $this->post('/login', ['email' => $email, 'password' => 'password'])
                ->assertRedirect(route('projects.index', ['stage' => $departmentCode]));

            $this->post('/logout');
        }
    }

    public function test_administrator_enters_the_project_overview_after_logging_in(): void
    {
        $department = Department::factory()->create(['code' => 'system_administration']);
        User::factory()->create(['department_id' => $department->id, 'email' => 'admin@example.com', 'password' => 'password', 'role' => 'administrator']);

        $this->post('/login', ['email' => 'admin@example.com', 'password' => 'password'])
            ->assertRedirect(route('projects.index'));
    }
}
