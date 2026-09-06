<?php

namespace Tests\Feature\Projects;

use App\Models\Department;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_product_department_can_manage_shared_product_categories(): void
    {
        $product = Department::factory()->create(['code' => 'market_research']);
        $operations = Department::factory()->create(['code' => 'website_operations']);
        $productUser = User::factory()->create(['department_id' => $product->id]);
        $operationsUser = User::factory()->create(['department_id' => $operations->id]);

        $this->actingAs($productUser)->post('/product-categories', ['name' => '厨房用品'])->assertRedirect();
        $category = ProductCategory::query()->firstOrFail();
        $this->assertSame('厨房用品', $category->name);

        $this->actingAs($operationsUser)->delete("/product-categories/{$category->id}")->assertForbidden();
        $this->actingAs($productUser)->delete("/product-categories/{$category->id}")->assertRedirect();
        $this->assertDatabaseCount('product_categories', 0);
    }
}
