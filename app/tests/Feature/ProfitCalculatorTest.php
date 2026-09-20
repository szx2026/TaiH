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
}
