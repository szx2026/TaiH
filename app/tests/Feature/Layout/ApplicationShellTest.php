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
            ->assertSee('市场研究部')
            ->assertSee('网站运营部')
            ->assertSee('内容创意部')
            ->assertSee('流量增长部')
            ->assertSee('/projects?stage=market_research', false)
            ->assertSee('/projects?stage=website_operations', false)
            ->assertSee('反馈中心');
    }
}
