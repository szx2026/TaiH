<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\ProductProject;
use App\Models\ProductSku;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfitCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('profit-calculator.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_profit_calculator(): void
    {
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id, 'role' => 'administrator']);

        $project = ProductProject::create([
            'project_code' => 'PP-202609-PROFIT01',
            'product_name' => '多功能削皮器',
            'market' => 'US',
            'priority' => 'initial_screening',
            'current_stage' => 'market_research',
            'status' => 'draft',
            'owner_department_id' => $department->id,
            'owner_user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        ProductSku::create([
            'product_project_id' => $project->id,
            'variant_name' => '标准版',
            'sku_code' => 'SKU-TEST-001',
            'purchase_price' => 18.50,
            'weight_g' => 280,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('profit-calculator.index'));

        $response->assertOk()
            ->assertSee('产品盈亏计算工具')
            ->assertSee('计算参数设置')
            ->assertSee('汇总测算指标')
            ->assertSee('产品明细列表')
            ->assertSee('多功能削皮器');
    }

    public function test_preloaded_project_is_passed_to_view(): void
    {
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id, 'role' => 'administrator']);

        $project = ProductProject::create([
            'project_code' => 'PP-202609-PROFIT02',
            'product_name' => '智能保温杯',
            'market' => 'US',
            'priority' => 'market_new',
            'current_stage' => 'market_research',
            'status' => 'draft',
            'owner_department_id' => $department->id,
            'owner_user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('profit-calculator.index', ['project' => $project->id]));

        $response->assertOk()
            ->assertSee('智能保温杯');
    }

    public function test_user_can_save_project_profit_data(): void
    {
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id, 'role' => 'administrator']);

        $project = ProductProject::create([
            'project_code' => 'PP-202609-PROFIT03',
            'product_name' => '便携榨汁杯',
            'market' => 'US',
            'priority' => 'market_new',
            'current_stage' => 'market_research',
            'status' => 'draft',
            'owner_department_id' => $department->id,
            'owner_user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $payload = [
            'settings' => [
                'exchangeRate' => 6.70,
                'feeRate' => 0.08,
                'refundRate' => 0.05,
                'logisticsBaseCny' => 24,
                'logisticsRateCnyKg' => 60,
            ],
            'products' => [
                [
                    'id' => 'prod-1',
                    'sku' => '标准版 (SKU-001)',
                    'costCny' => 25.0,
                    'weightG' => 350,
                    'priceUsd' => 19.99,
                    'storeShippingUsd' => 0,
                ],
            ],
        ];

        $response = $this->actingAs($user)
            ->postJson(route('profit-calculator.save', $project), $payload);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $project->refresh();
        $this->assertNotNull($project->profit_data);
        $this->assertEquals(6.70, $project->profit_data['settings']['exchangeRate']);
        $this->assertEquals(19.99, $project->profit_data['products'][0]['priceUsd']);
    }

    public function test_can_create_new_project_profit_calculation_and_sync_skus(): void
    {
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id, 'role' => 'administrator']);

        $payload = [
            'product_name' => '磁吸折叠手机支架',
            'settings' => [
                'exchangeRate' => 7.20,
                'feeRate' => 0.03,
                'refundRate' => 0.05,
                'logisticsBaseCny' => 30,
                'logisticsRateCnyKg' => 50,
            ],
            'products' => [
                [
                    'id' => 'prod-1',
                    'sku' => 'STAND-MAG-BLK',
                    'costCny' => 15.50,
                    'weightG' => 120,
                    'priceUsd' => 24.99,
                    'storeShippingUsd' => 0,
                ],
                [
                    'id' => 'prod-2',
                    'sku' => 'STAND-MAG-SLV',
                    'costCny' => 16.00,
                    'weightG' => 120,
                    'priceUsd' => 24.99,
                    'storeShippingUsd' => 0,
                ],
            ],
        ];

        $response = $this->actingAs($user)
            ->postJson(route('profit-calculator.save-new'), $payload);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $project = ProductProject::where('product_name', '磁吸折叠手机支架')->first();
        $this->assertNotNull($project);
        $this->assertNotNull($project->profit_data);
        $this->assertCount(2, $project->skus);

        $skuBlk = $project->skus()->where('sku_code', 'STAND-MAG-BLK')->first();
        $this->assertNotNull($skuBlk);
        $this->assertEquals(15.50, $skuBlk->purchase_price);
        $this->assertEquals(120, $skuBlk->weight_g);

        // Verify that order profit calculator can see these SKUs
        $orderCalcResponse = $this->actingAs($user)
            ->get(route('order-profit-calculator.index'));

        $orderCalcResponse->assertOk()
            ->assertSee('STAND-MAG-BLK')
            ->assertSee('STAND-MAG-SLV')
            ->assertSee('磁吸折叠手机支架');
    }
}
