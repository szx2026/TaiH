# 产品工作区协作优化 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** 让产品资料、跨部门待办、阶段交接和共享资料在四个部门工作台中保持同步、可追溯且易于浏览。

**Architecture:** 项目级资料放入 `product_projects`，SKU 保持独立记录并通过受限接口编辑。待办中心聚合现有反馈和决策记录；阶段推进继续以明确的提交操作为唯一入口。页面在服务端输出完整资料，JavaScript 只负责刷新提示和小型交互。

**Tech Stack:** Laravel 12、PHP 8.2、Blade、Tailwind/Vite、PHPUnit。

**Spec:** `docs/superpowers/specs/2026-09-07-product-workspace-collaboration-design.md`

## Global Constraints

- 产品标题原始值不修改，关键词只作为显示后缀。
- 只有产品部成员或管理员可编辑、删除 SKU；只有运营部成员或管理员可撤回运营部规格请求。
- 阶段只能由当前负责部门明确提交推进，保存表单不得改变阶段。
- 所有跨部门资料必须通过项目归属校验，外部链接必须使用 `target="_blank" rel="noreferrer"`。

---

### Task 1: 项目关键词与详情页参考资料

**Files:**
- Create: `database/migrations/2026_09_07_000037_add_product_reference_fields_to_product_projects_table.php`
- Modify: `app/Models/ProductProject.php`
- Modify: `app/Http/Controllers/ResearchSourceController.php`
- Modify: `resources/views/projects/index.blade.php`
- Test: `tests/Feature/Projects/ProductInformationTest.php`

**Interfaces:**
- Produces: `ProductProject::$keywords`, `ProductProject::$detail_reference_url`, `ProductProject::display_name`。

- [ ] **Step 1: Write the failing test**

```php
public function test_product_department_saves_keywords_and_a_detail_page_reference(): void
{
    $this->actingAs($productUser)->post("/projects/{$project->id}/research-sources", [
        'platform' => 'tiktok', 'url' => 'https://tiktok.com/example', 'evidence_note' => '测试证据',
        'keywords' => '轮胎灯, 彩色轮毂灯',
        'detail_reference_url' => 'https://example.com/products/reference',
    ])->assertRedirect();

    $this->assertDatabaseHas('product_projects', ['id' => $project->id, 'keywords' => '轮胎灯, 彩色轮毂灯']);
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Projects/ProductInformationTest.php --compact`

Expected: FAIL because the project table has no reference fields or controller validation rejects them.

- [ ] **Step 3: Write minimal implementation**

```php
$table->text('keywords')->nullable()->after('product_name');
$table->string('detail_reference_url', 2048)->nullable()->after('keywords');

public function getDisplayNameAttribute(): string
{
    return filled($this->keywords) ? "{$this->product_name} · {$this->keywords}" : $this->product_name;
}
```

Validate the optional URL and keywords in `ResearchSourceController`, update the project in the existing transaction-free save path, and render editable fields only for product department users.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/Projects/ProductInformationTest.php --compact`

Expected: PASS; the product header and project selector use `display_name`.

- [ ] **Step 5: Commit**

```bash
git add database/migrations app/Models/ProductProject.php app/Http/Controllers/ResearchSourceController.php resources/views/projects/index.blade.php tests/Feature/Projects/ProductInformationTest.php
git commit -m "feat: add product keywords and detail references"
```

### Task 2: SKU 编辑、删除和可撤回规格请求

**Files:**
- Modify: `app/Http/Controllers/ProductSkuController.php`
- Modify: `app/Http/Controllers/ProjectDecisionController.php`
- Modify: `routes/web.php`
- Modify: `resources/views/projects/index.blade.php`
- Test: `tests/Feature/Projects/ProductSkuManagementTest.php`
- Test: `tests/Feature/Projects/ProjectDecisionTest.php`

**Interfaces:**
- Produces: `PATCH /projects/{project}/skus/{sku}` and `PATCH /projects/{project}/decisions/{decision}/requested-specifications/{specification}/withdraw`.

- [ ] **Step 1: Write failing tests**

```php
$this->actingAs($productUser)->patch("/projects/{$project->id}/skus/{$sku->id}", [
    'sku_code' => 'NC-NEW-01', 'variant_name' => '两件套', 'purchase_price' => 19.9, 'weight_g' => 180,
])->assertRedirect();

$this->actingAs($operationsUser)->patch("/projects/{$project->id}/decisions/{$decision->id}/requested-specifications/礼盒装/withdraw")
    ->assertRedirect();
```

Assert that SKU values change, the withdrawn pending specification is removed from decision details, and a specification whose SKU exists returns 422.

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test tests/Feature/Projects/ProductSkuManagementTest.php tests/Feature/Projects/ProjectDecisionTest.php --compact`

Expected: FAIL with 404 because the update and withdrawal routes do not exist.

- [ ] **Step 3: Implement the restricted actions**

```php
abort_unless($sku->product_project_id === $project->id, 404);
$sku->update($data);

$requested = collect(data_get($decision->details, 'requested_specifications', []));
abort_if($project->skus()->where('variant_name', $specification)->exists(), 422);
$decision->update(['details' => [...$decision->details, 'requested_specifications' => $requested->reject(fn ($item) => $item === $specification)->values()->all()]]);
```

Record `sku.updated` and `specification_request.withdrawn` activities. Add inline edit and delete forms to product SKU cards; add an operations-only withdraw button for each still-pending requested specification.

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test tests/Feature/Projects/ProductSkuManagementTest.php tests/Feature/Projects/ProjectDecisionTest.php --compact`

Expected: PASS; unauthorized department users receive 403.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/ProductSkuController.php app/Http/Controllers/ProjectDecisionController.php routes/web.php resources/views/projects/index.blade.php tests/Feature/Projects/ProductSkuManagementTest.php tests/Feature/Projects/ProjectDecisionTest.php
git commit -m "feat: manage SKU records and pending specifications"
```

### Task 3: 全局待处理中心与阶段交接

**Files:**
- Modify: `app/Http/Controllers/FeedbackCenterController.php`
- Create: `app/Queries/DepartmentWorkQueueQuery.php`
- Create: `resources/views/feedback/index.blade.php`
- Modify: `app/Http/Controllers/ProjectWorkflowController.php`
- Modify: `resources/views/projects/index.blade.php`
- Test: `tests/Feature/FeedbackCenterTest.php`
- Test: `tests/Feature/Projects/ProjectWorkflowTest.php`

**Interfaces:**
- Produces: `DepartmentWorkQueueQuery::for(User $user): Collection` and a rendered `/feedback` worklist.

- [ ] **Step 1: Write failing tests**

```php
$this->actingAs($operationsUser)->get('/feedback')
    ->assertOk()
    ->assertSee('全部待处理问题')
    ->assertSee('请确认产品规格')
    ->assertSee(route('projects.index', ['stage' => 'website_operations', 'project' => $project]), false);

$this->actingAs($productUser)->post("/projects/{$project->id}/submit", ['target_stage' => 'website_operations'])
    ->assertRedirect();
$this->assertDatabaseHas('product_projects', ['id' => $project->id, 'current_stage' => 'website_operations']);
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test tests/Feature/FeedbackCenterTest.php tests/Feature/Projects/ProjectWorkflowTest.php --compact`

Expected: FAIL because feedback currently redirects and no worklist is rendered.

- [ ] **Step 3: Implement the queue and explicit progression**

```php
return collect()
    ->concat($feedbackItems)
    ->concat($decisionItems)
    ->sortByDesc('created_at')
    ->values();
```

Make `FeedbackCenterController::index` return the new view. Keep `SubmitProjectStage` as the only code that writes `current_stage`; render one clear “完成并提交” action for the current department and use its response to update the progress rail.

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test tests/Feature/FeedbackCenterTest.php tests/Feature/Projects/ProjectWorkflowTest.php --compact`

Expected: PASS; the queue contains all non-resolved work assigned to the signed-in department.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/FeedbackCenterController.php app/Queries/DepartmentWorkQueueQuery.php resources/views/feedback/index.blade.php app/Http/Controllers/ProjectWorkflowController.php resources/views/projects/index.blade.php tests/Feature/FeedbackCenterTest.php tests/Feature/Projects/ProjectWorkflowTest.php
git commit -m "feat: add department work queue and stage handoff"
```

### Task 4: 共享资料、素材下载和筛选结果

**Files:**
- Modify: `app/Http/Controllers/CreativeAssetController.php`
- Modify: `app/Http/Controllers/ProductProjectController.php`
- Modify: `resources/views/projects/index.blade.php`
- Test: `tests/Feature/Projects/CreativeAssetTest.php`
- Test: `tests/Feature/Projects/ProductCenterTest.php`

**Interfaces:**
- Produces: a project-owned asset download response and `filteredProjects` available to the workspace view.

- [ ] **Step 1: Write failing tests**

```php
$this->actingAs($user)->get("/projects/{$project->id}/creative-assets/{$asset->id}/download")
    ->assertOk()
    ->assertHeader('content-disposition');

$this->actingAs($user)->get('/projects?stage=market_research&category=灯具')
    ->assertSee('匹配项目（2）')
    ->assertSee('灯具项目 A')
    ->assertSee('灯具项目 B');
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test tests/Feature/Projects/CreativeAssetTest.php tests/Feature/Projects/ProductCenterTest.php --compact`

Expected: FAIL because the rendered workspace has no full filter result list or a local asset path is not streamed correctly.

- [ ] **Step 3: Implement shared resource rendering**

```php
if ($asset->storage_path && Storage::disk($asset->storage_disk)->exists($asset->storage_path)) {
    return Storage::disk($asset->storage_disk)->download($asset->storage_path, $asset->original_filename ?? basename($asset->storage_path));
}
abort(404);
```

Add original filename storage if absent, replace invalid file download links with reference-link actions, render every landing-page preview image, use `break-all` and clickable anchors for URLs, and render a selectable `匹配项目` list from `$projects`.

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test tests/Feature/Projects/CreativeAssetTest.php tests/Feature/Projects/ProductCenterTest.php --compact`

Expected: PASS; all matching projects are present and a stored asset returns a download response.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/CreativeAssetController.php app/Http/Controllers/ProductProjectController.php resources/views/projects/index.blade.php tests/Feature/Projects/CreativeAssetTest.php tests/Feature/Projects/ProductCenterTest.php
git commit -m "feat: improve shared resources and filter results"
```

### Task 5: 刷新交互与固定项目头

**Files:**
- Modify: `resources/css/app.css`
- Modify: `resources/views/projects/index.blade.php`
- Test: `tests/Feature/Projects/ProductCenterTest.php`

**Interfaces:**
- Produces: `data-workspace-refresh` button and sticky desktop project header.

- [ ] **Step 1: Write the failing page test**

```php
$this->actingAs($user)->get("/projects?stage=content_creative&project={$project->id}")
    ->assertOk()
    ->assertSee('刷新最新内容')
    ->assertSee('data-workspace-refresh', false);
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Projects/ProductCenterTest.php --compact`

Expected: FAIL because no workspace refresh control is rendered.

- [ ] **Step 3: Implement the client-only refresh summary**

```js
const key = `nc-erp:workspace:${stage}:last-seen`;
const previousSeenAt = localStorage.getItem(key);
refreshButton.addEventListener('click', () => {
  localStorage.setItem(key, new Date().toISOString());
  window.location.reload();
});
```

Render update timestamps from the existing records, compare them with `previousSeenAt` on load, and display the count. Add `.project-summary-header { position: sticky; top: 0.75rem; z-index: 20; }` only above the mobile breakpoint.

- [ ] **Step 4: Run page test and build**

Run: `php artisan test tests/Feature/Projects/ProductCenterTest.php --compact && npm run build`

Expected: PASS and Vite exits with code 0.

- [ ] **Step 5: Commit**

```bash
git add resources/css/app.css resources/views/projects/index.blade.php tests/Feature/Projects/ProductCenterTest.php
git commit -m "feat: add workspace refresh and sticky project header"
```

### Task 6: Full verification and production release

**Files:**
- Modify: generated `public/build/manifest.json` and assets only through `npm run build`

- [ ] **Step 1: Run complete verification**

Run: `php artisan test --compact && npm run build && git diff --check`

Expected: all tests pass, build succeeds, and `git diff --check` has no output.

- [ ] **Step 2: Deploy source, migrations, and build assets**

Run the existing SiteGround deployment procedure: upload changed Laravel source, run `php artisan migrate --force`, sync Vite manifest/assets to both `public/build` and `public_html/build`, then run `php artisan optimize`.

- [ ] **Step 3: Verify the deployed site**

Run: `curl -I https://aitoolgroup.com/login` and `curl -I https://aitoolgroup.com/build/assets/<manifest-css-file>`.

Expected: both requests return HTTP 200.

- [ ] **Step 4: Commit release-only generated-source exclusions if needed**

Do not commit `.demo.env`, `.superpowers/`, or unrelated parent `.gitignore` changes. Commit only tracked application, migration, test, documentation, and deliberate source changes.
