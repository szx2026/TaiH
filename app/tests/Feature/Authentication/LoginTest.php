<?php

namespace Tests\Feature\Authentication;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_see_the_branded_login_workspace(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('NC ERP', false)
            ->assertSee('跨境产品增长协作系统', false)
            ->assertSee('登录工作台', false)
            ->assertSee('越努力，越幸运', false)
            ->assertSee('class="login-page"', false)
            ->assertSee('class="login-statement-line"', false)
            ->assertSee('class="login-card"', false);
    }

    public function test_a_member_can_sign_in_with_email_and_password(): void
    {
        $user = User::factory()->create([
            'email' => 'member@example.com',
            'password' => 'secure-password',
        ]);

        $this->post('/login', [
            'email' => 'member@example.com',
            'password' => 'secure-password',
        ])->assertRedirect('/projects');

        $this->assertAuthenticatedAs($user);
    }
}
