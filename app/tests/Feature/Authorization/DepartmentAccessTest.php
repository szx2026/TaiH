<?php

namespace Tests\Feature\Authorization;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_belongs_to_a_department_and_has_a_role(): void
    {
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create([
            'department_id' => $department->id,
            'role' => 'member',
        ]);

        $this->assertSame('market_research', $user->department->code);
        $this->assertTrue($user->hasRole('member'));
        $this->assertFalse($user->hasRole('administrator'));
    }
}
