<?php

namespace Tests\Feature\Layout;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_member_sees_the_erp_navigation_shell(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/projects')
            ->assertOk()
            ->assertSee('工作台')
            ->assertSee('产品中心')
            ->assertSee('素材中心')
            ->assertSee('投放中心')
            ->assertSee('反馈中心');
    }
}
