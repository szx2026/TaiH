<?php

namespace Tests\Feature\Authentication;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

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
