<?php

namespace Tests\Feature\Projects;

use App\Models\Department;
use App\Models\ProductProject;
use App\Models\ProductSku;
use App\Models\ProductSource;
use App\Models\ProjectDecision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSkuManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_department_updates_an_existing_product_specification(): void
    {
        [$project, $productUser, $sku] = $this->projectWithSku();

        $this->actingAs($productUser)
            ->patch("/projects/{$project->id}/skus/{$sku->id}", [
                'sku_code' => 'NC-EDIT-01',
                'variant_name' => '升级两件套',
                'purchase_price' => 18.50,
                'weight_g' => 240,
            ])
            ->assertRedirect(route('projects.index', ['stage' => 'market_research', 'project' => $project]));

        $this->assertDatabaseHas('product_skus', [
            'id' => $sku->id,
            'sku_code' => 'NC-EDIT-01',
            'variant_name' => '升级两件套',
            'purchase_price' => 18.5,
            'weight_g' => 240,
        ]);
        $this->assertDatabaseHas('project_activities', ['product_project_id' => $project->id, 'actor_id' => $productUser->id, 'event' => 'sku.updated']);
    }

    public function test_operations_can_withdraw_an_unfulfilled_requested_specification(): void
    {
        [$project, $productUser] = $this->projectWithSku();
        $operations = Department::factory()->create(['code' => 'website_operations']);
        $operationsUser = User::factory()->create(['department_id' => $operations->id]);
        $decision = ProjectDecision::create([
            'product_project_id' => $project->id,
            'decision_type' => 'specification',
            'requested_from_stage' => 'website_operations',
            'title' => '需要新增规格',
            'status' => 'resolved',
            'details' => ['requested_specifications' => ['礼盒装']],
            'created_by' => $productUser->id,
            'responded_by' => $operationsUser->id,
            'responded_at' => now(),
        ]);

        $this->actingAs($operationsUser)
            ->patch("/projects/{$project->id}/decisions/{$decision->id}/requested-specifications/礼盒装/withdraw")
            ->assertRedirect(route('projects.index', ['stage' => 'website_operations', 'project' => $project]));

        $this->assertSame(['礼盒装'], $decision->fresh()->details['withdrawn_requested_specifications']);
        $this->assertDatabaseHas('project_activities', ['product_project_id' => $project->id, 'actor_id' => $operationsUser->id, 'event' => 'specification_request.withdrawn']);
    }

    private function projectWithSku(): array
    {
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $project = ProductProject::create(['project_code' => 'PP-202609-SKU-MANAGE', 'product_name' => '规格管理产品', 'market' => 'US', 'priority' => 'medium', 'current_stage' => 'market_research', 'status' => 'draft', 'owner_department_id' => $department->id, 'owner_user_id' => $user->id, 'created_by' => $user->id]);
        $source = ProductSource::create(['product_project_id' => $project->id, 'supplier_url' => 'https://detail.1688.com/offer/sku-manage.html', 'supplier_name' => '规格管理工厂', 'product_name' => '规格管理产品', 'currency' => 'CNY', 'notes' => '测试规格编辑。', 'created_by' => $user->id]);
        $sku = ProductSku::create(['product_project_id' => $project->id, 'product_source_id' => $source->id, 'sku_code' => 'NC-ORIGINAL-01', 'variant_name' => '标准款', 'purchase_price' => 12, 'weight_g' => 180, 'sku_status' => 'internal_confirmed', 'created_by' => $user->id]);

        return [$project, $user, $sku];
    }
}
