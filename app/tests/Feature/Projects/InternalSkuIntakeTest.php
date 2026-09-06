<?php

namespace Tests\Feature\Projects;

use App\Models\Department;
use App\Models\ProductProject;
use App\Models\ProductSource;
use App\Models\ProductSku;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalSkuIntakeTest extends TestCase
{
    use RefreshDatabase;

    public function test_market_research_can_fill_an_internal_sku_after_recording_its_1688_product_specification(): void
    {
        [$user, $project] = $this->marketResearchProject();
        $pendingSku = $this->pendingSpecification($project, $user);

        $this->actingAs($user)
            ->post("/projects/{$project->id}/skus", [
                'product_sku_id' => $pendingSku->id,
                'sku_code' => 'NC03342609026143',
            ])
            ->assertRedirect(route('projects.index', ['stage' => 'market_research', 'project' => $project]));

        $this->assertDatabaseHas('product_skus', [
            'product_project_id' => $project->id,
            'product_source_id' => $pendingSku->product_source_id,
            'sku_code' => 'NC03342609026143',
            'variant_name' => '夜灯 + 3 影片',
            'sku_status' => 'internal_confirmed',
            'created_by' => $user->id,
        ]);
        $this->assertDatabaseHas('project_activities', [
            'product_project_id' => $project->id,
            'actor_id' => $user->id,
            'event' => 'sku.imported_from_product_system',
        ]);
    }

    public function test_internal_sku_code_must_be_unique_within_its_project_even_without_a_source(): void
    {
        [$user, $project] = $this->marketResearchProject();
        $pendingSku = $this->pendingSpecification($project, $user);
        ProductSku::create([
            'product_project_id' => $project->id,
            'product_source_id' => null,
            'sku_code' => 'NC03342609026143',
            'variant_name' => '夜灯 + 3 影片',
            'sku_status' => 'imported',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->from(route('projects.workspace', ['project' => $project, 'tab' => 'research']))
            ->post("/projects/{$project->id}/skus", [
                'product_sku_id' => $pendingSku->id,
                'sku_code' => 'NC03342609026143',
            ])
            ->assertRedirect(route('projects.workspace', ['project' => $project, 'tab' => 'research']))
            ->assertSessionHasErrors('sku_code');
    }

    public function test_legacy_supplier_sku_duplicates_are_preserved_but_cannot_be_imported_again(): void
    {
        [$user, $project] = $this->marketResearchProject();
        $pendingSku = $this->pendingSpecification($project, $user);
        $firstSource = ProductSource::create([
            'product_project_id' => $project->id,
            'supplier_url' => 'https://detail.1688.com/offer/1.html',
            'currency' => 'CNY',
            'created_by' => $user->id,
        ]);
        $secondSource = ProductSource::create([
            'product_project_id' => $project->id,
            'supplier_url' => 'https://detail.1688.com/offer/2.html',
            'currency' => 'CNY',
            'created_by' => $user->id,
        ]);

        ProductSku::create([
            'product_project_id' => $project->id,
            'product_source_id' => $firstSource->id,
            'sku_code' => 'NC03342609026143',
            'variant_name' => '夜灯 + 3 影片',
            'sku_status' => 'imported',
            'created_by' => $user->id,
        ]);
        ProductSku::create([
            'product_project_id' => $project->id,
            'product_source_id' => $secondSource->id,
            'sku_code' => 'NC03342609026143',
            'variant_name' => '夜灯 + 12 影片',
            'sku_status' => 'imported',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->from(route('projects.workspace', ['project' => $project, 'tab' => 'research']))
            ->post("/projects/{$project->id}/skus", [
                'product_sku_id' => $pendingSku->id,
                'sku_code' => 'NC03342609026143',
            ])
            ->assertRedirect(route('projects.workspace', ['project' => $project, 'tab' => 'research']))
            ->assertSessionHasErrors('sku_code');

        $this->assertDatabaseCount('product_skus', 3);
    }

    public function test_only_market_research_members_and_administrators_can_import_internal_skus(): void
    {
        $operations = Department::factory()->create(['code' => 'website_operations']);
        $user = User::factory()->create(['department_id' => $operations->id, 'role' => 'member']);
        $project = $this->projectFor($user, $operations);

        $this->actingAs($user)
            ->post("/projects/{$project->id}/skus", [
                'sku_code' => 'NC03342609026143',
                'variant_name' => '夜灯 + 3 影片',
            ])
            ->assertForbidden();
    }

    public function test_product_department_cannot_send_specification_to_operations_until_an_internal_sku_is_filled(): void
    {
        [$user, $project] = $this->marketResearchProject();
        $this->pendingSpecification($project, $user);

        $this->actingAs($user)
            ->post("/projects/{$project->id}/decisions", [
                'decision_type' => 'specification',
                'requested_from_stage' => 'website_operations',
                'title' => '请确认规格',
                'details' => '待确认',
            ])
            ->assertStatus(422);
    }

    public function test_an_administrator_can_import_an_internal_sku(): void
    {
        $operations = Department::factory()->create(['code' => 'website_operations']);
        $user = User::factory()->create(['department_id' => $operations->id, 'role' => 'administrator']);
        $project = $this->projectFor($user, $operations);
        $pendingSku = $this->pendingSpecification($project, $user);

        $this->actingAs($user)
            ->post("/projects/{$project->id}/skus", [
                'product_sku_id' => $pendingSku->id,
                'sku_code' => 'NC03342609026143',
            ])
            ->assertRedirect(route('projects.index', ['stage' => 'market_research', 'project' => $project]));

        $this->assertDatabaseHas('product_skus', [
            'product_project_id' => $project->id,
            'product_source_id' => $pendingSku->product_source_id,
            'created_by' => $user->id,
        ]);
    }

    /** @return array{User, ProductProject} */
    private function marketResearchProject(): array
    {
        $department = Department::factory()->create(['code' => 'market_research']);
        $user = User::factory()->create(['department_id' => $department->id, 'role' => 'member']);

        return [$user, $this->projectFor($user, $department)];
    }

    private function projectFor(User $user, Department $department): ProductProject
    {
        return ProductProject::create([
            'project_code' => 'PP-202609-INTSKU-'.$user->id,
            'product_name' => '星空投影灯',
            'market' => 'US',
            'priority' => 'high',
            'current_stage' => 'market_research',
            'status' => 'in_progress',
            'owner_department_id' => $department->id,
            'owner_user_id' => $user->id,
            'created_by' => $user->id,
        ]);
    }

    private function pendingSpecification(ProductProject $project, User $user): ProductSku
    {
        $source = ProductSource::create([
            'product_project_id' => $project->id,
            'supplier_url' => 'https://detail.1688.com/offer/pending-spec.html',
            'supplier_name' => '1688 测试工厂',
            'currency' => 'CNY',
            'created_by' => $user->id,
        ]);

        return ProductSku::create([
            'product_project_id' => $project->id,
            'product_source_id' => $source->id,
            'sku_code' => null,
            'variant_name' => '夜灯 + 3 影片',
            'sku_status' => 'source_recorded',
            'created_by' => $user->id,
        ]);
    }
}
