<?php

namespace Tests\Feature\Administration;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MemberManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_administrator_can_create_a_member_for_a_department(): void
    {
        $admin = User::factory()->create(['role' => 'administrator']);
        $department = Department::factory()->create(['code' => 'content_creative']);

        $this->actingAs($admin)
            ->post('/members', [
                'name' => '李创意',
                'email' => 'creative@example.com',
                'password' => 'temporary-password',
                'department_id' => $department->id,
                'role' => 'member',
            ])
            ->assertRedirect('/members');

        $member = User::query()->where('email', 'creative@example.com')->firstOrFail();
        $this->assertSame('李创意', $member->name);
        $this->assertSame($department->id, $member->department_id);
        $this->assertSame('member', $member->role);
        $this->assertTrue(Hash::check('temporary-password', $member->password));
    }
}
