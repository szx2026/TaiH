<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\ProductProject;
use App\Models\ProductSku;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderProfitCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('order-profit-calculator.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_order_profit_calculator(): void
    {
        $department = Department::factory()->create(['code' => 'traffic_growth']);
        $user = User::factory()->create(['department_id' => $department->id, 'role' => 'administrator']);

        $project = ProductProject::create([
            'project_code' => 'PP-202609-ORD01',
            'product_name' => '磁吸车载无线充',
            'market' => 'US',
            'priority' => 'initial_screening',
            'current_stage' => 'traffic_growth',
            'status' => 'draft',
            'owner_department_id' => $department->id,
            'owner_user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $sku = ProductSku::create([
            'product_project_id' => $project->id,
            'variant_name' => '曜石黑',
            'sku_code' => 'MWC-BLK',
            'purchase_price' => 25.50,
            'weight_g' => 160,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('order-profit-calculator.index'));

        $response->assertOk()
            ->assertViewIs('profit-calculator.orders')
            ->assertViewHas('knownSkus')
            ->assertSee('每日订单利润核算')
            ->assertSee('导入 Shopify 当日订单 (CSV)')
            ->assertSee('今日 FB 广告花费与核算参数')
            ->assertSee('MWC-BLK')
            ->assertSee('磁吸车载无线充');
    }
}
