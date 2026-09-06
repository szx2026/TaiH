<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\ProductionAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionAccountSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_only_the_five_configured_production_accounts(): void
    {
        putenv('NC_ERP_INITIAL_PASSWORD=temporary-test-password');

        app(ProductionAccountSeeder::class)->run();

        $this->assertSame(5, User::query()->count());
        $this->assertDatabaseHas('users', [
            'email' => '745551014@qq.com',
            'role' => 'manager',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'szxshop00@gmail.com',
            'role' => 'administrator',
            'department_id' => null,
        ]);
    }
}
