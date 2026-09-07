# 产品工作区协作优化 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** 让四个部门可维护准确产品资料、集中处理待办，并清晰完成项目交接。

**Architecture:** 关键词与详情页参考链接归属 `ProductProject`；规格维护归属 `ProductSku`；运营部规格请求归属 `ProjectDecision`。`FeedbackCenterController` 从重定向改为独立待办清单，工作台保持为项目资料与交接操作入口。

**Tech Stack:** Laravel 12、PHP 8.2、Blade、Tailwind/Vite、Laravel feature tests。

**Spec:** `docs/superpowers/specs/2026-09-07-product-workspace-collaboration-design.md`

## Global Constraints

- 不新增第三方依赖。
- 关键词可输入多个，逗号分隔保存，显示为“产品标题 · 关键词”，不改 `product_name` 原值。
- SKU 编辑/删除仅产品部或管理员；未生成内部 SKU 的规格请求仅运营部或管理员可撤回。
- 所有外部链接均使用 `target="_blank" rel="noreferrer"` 并支持长链接换行。
- 只有当前阶段部门点击“完成并提交”才会推进项目阶段。
- 每个写操作保留项目活动记录。

---

### Task 1: 项目关键词、详情页参考与展示标题

**Files:**
- Create: `database/migrations/2026_09_07_000037_add_product_reference_fields_to_product_projects_table.php`
- Modify: `app/Models/ProductProject.php`
- Modify: `app/Http/Controllers/ResearchSourceController.php`
- Modify: `resources/views/projects/index.blade.php`
- Test: `tests/Feature/Projects/ProductReferenceMetadataTest.php`

**Interfaces:** Produces nullable `ProductProject.keywords` and `ProductProject.detail_reference_url`; consumes the two form fields in `ResearchSourceController::store`.

- [ ] **Step 1: Write a failing feature test**

```php
$this->actingAs($productUser)->post("/projects/{$project->id}/research-sources", [
    'platform' => 'tiktok', 'url' => 'https://example.com/evidence', 'evidence_note' => '需求稳定',
    'keywords' => '轮胎灯, 自行车配件', 'detail_reference_url' => 'https://example.com/products/reference',
])->assertRedirect();
$this->assertDatabaseHas('product_projects', ['id' => $project->id, 'keywords' => '轮胎灯, 自行车配件']);
```

- [ ] **Step 2: Run test and confirm RED**

Run: `docker exec erp-redesign-live php artisan test tests/Feature/Projects/ProductReferenceMetadataTest.php --compact`

Expected: FAIL because neither column nor validation exists.

- [ ] **Step 3: Implement the smallest persistent fields and form update**

```php
$table->string('keywords', 1000)->nullable()->after('product_name');
$table->string('detail_reference_url', 2048)->nullable()->after('keywords');
$project->update(Arr::only($data, ['keywords', 'detail_reference_url']));
```

Render both inputs in the product information form. Add the keyword suffix in project selector cards and headers. Render the reference URL as a clickable shared resource.

- [ ] **Step 4: Run the focused test and confirm GREEN**

Run: `docker exec erp-redesign-live php artisan test tests/Feature/Projects/ProductReferenceMetadataTest.php --compact`

Expected: PASS.

- [ ] **Step 5: Commit the task**

Run: `git add database/migrations/2026_09_07_000037_add_product_reference_fields_to_product_projects_table.php app/Models/ProductProject.php app/Http/Controllers/ResearchSourceController.php resources/views/projects/index.blade.php tests/Feature/Projects/ProductReferenceMetadataTest.php && git commit -m "feat: add product keywords and detail references"`

### Task 2: SKU 编辑、拆分展示和运营部规格撤回

**Files:**
- Modify: `routes/web.php`
- Modify: `app/Http/Controllers/ProductSkuController.php`
- Modify: `app/Http/Controllers/ProjectDecisionController.php`
- Modify: `resources/views/projects/index.blade.php`
- Test: `tests/Feature/Projects/ProductSkuManagementTest.php`
- Test: `tests/Feature/Projects/ProjectDecisionTest.php`

**Interfaces:** Produces `PATCH /projects/{project}/skus/{sku}` and a withdraw request endpoint. `ProjectDecision.details.withdrawn_requested_specifications` stores withdrawn values.

- [ ] **Step 1: Write failing tests**

```php
$this->actingAs($productUser)->patch("/projects/{$project->id}/skus/{$sku->id}", [
    'sku_code' => 'NC-EDIT-01', 'variant_name' => '升级两件套', 'purchase_price' => 18.50, 'weight_g' => 240,
])->assertRedirect();
$this->assertDatabaseHas('product_skus', ['id' => $sku->id, 'variant_name' => '升级两件套', 'weight_g' => 240]);
```

```php
$this->actingAs($operationsUser)->patch("/projects/{$project->id}/decisions/{$decision->id}/requested-specifications/礼盒装", [])
    ->assertRedirect();
```

- [ ] **Step 2: Run tests and confirm RED**

Run: `docker exec erp-redesign-live php artisan test tests/Feature/Projects/ProductSkuManagementTest.php tests/Feature/Projects/ProjectDecisionTest.php --compact`

Expected: FAIL with missing update/withdrawal routes.

- [ ] **Step 3: Implement route, validation and activity records**

```php
$data = $request->validate([
    'sku_code' => ['required', 'string', 'max:100', Rule::unique('product_skus')->ignore($sku->id)->where('product_project_id', $project->id)],
    'variant_name' => ['required', 'string', 'max:255'],
    'purchase_price' => ['nullable', 'numeric', 'min:0'], 'weight_g' => ['nullable', 'integer', 'min:0'],
]);
$sku->update($data);
```

Render edit controls on product-department SKU cards. Split shared-material presentation into separate `产品规格` and `内部 SKU` fields. Allow withdrawal only when no project SKU matches the requested variant name; append it to `withdrawn_requested_specifications` and record the event.

- [ ] **Step 4: Run focused tests and confirm GREEN**

Run: `docker exec erp-redesign-live php artisan test tests/Feature/Projects/ProductSkuManagementTest.php tests/Feature/Projects/ProjectDecisionTest.php --compact`

Expected: PASS.

- [ ] **Step 5: Commit the task**

Run: `git add routes/web.php app/Http/Controllers/ProductSkuController.php app/Http/Controllers/ProjectDecisionController.php resources/views/projects/index.blade.php tests/Feature/Projects/ProductSkuManagementTest.php tests/Feature/Projects/ProjectDecisionTest.php && git commit -m "feat: manage product specifications and withdrawals"`

### Task 3: 全局待办中心与阶段提交反馈

**Files:**
- Modify: `app/Http/Controllers/FeedbackCenterController.php`
- Create: `resources/views/feedback/index.blade.php`
- Modify: `app/Http/Controllers/ProjectWorkflowController.php`
- Modify: `resources/views/projects/index.blade.php`
- Test: `tests/Feature/Projects/ProjectFeedbackCenterTest.php`
- Test: `tests/Feature/Projects/StageHandoffTest.php`

**Interfaces:** Produces HTML `GET /feedback` worklist. Consumes unresolved `OptimizationFeedback`, open decisions and unfulfilled specification requests. `projects.submit` remains the only stage-changing route.

- [ ] **Step 1: Write failing feedback-center test**

```php
$this->actingAs($operationsUser)->get('/feedback')
    ->assertOk()->assertSee('全部待处理事项')
    ->assertSee($firstProject->product_name)->assertSee($secondProject->product_name)
    ->assertSee('去处理');
```

- [ ] **Step 2: Run test and confirm RED**

Run: `docker exec erp-redesign-live php artisan test tests/Feature/Projects/ProjectFeedbackCenterTest.php --compact`

Expected: FAIL because `/feedback` currently redirects to one project.

- [ ] **Step 3: Implement worklist view and retain explicit handoff**

```php
return view('feedback.index', [
    'items' => $this->worklistFor($request->user()),
    'stage' => $request->user()->department?->code,
]);
```

Each item includes product name, source, time, reason and a projects URL with both `stage` and `project`. Ensure submit controls only render for the current stage owner after existing completion requirements are met, so progress updates when the user submits rather than on incidental saves.

- [ ] **Step 4: Run focused tests and confirm GREEN**

Run: `docker exec erp-redesign-live php artisan test tests/Feature/Projects/ProjectFeedbackCenterTest.php tests/Feature/Projects/StageHandoffTest.php --compact`

Expected: PASS.

- [ ] **Step 5: Commit the task**

Run: `git add app/Http/Controllers/FeedbackCenterController.php resources/views/feedback/index.blade.php app/Http/Controllers/ProjectWorkflowController.php resources/views/projects/index.blade.php tests/Feature/Projects/ProjectFeedbackCenterTest.php tests/Feature/Projects/StageHandoffTest.php && git commit -m "feat: add department feedback worklists"`

### Task 4: 共享素材下载、详情页预览和链接可读性

**Files:**
- Modify: `app/Http/Controllers/CreativeAssetController.php`
- Modify: `resources/views/projects/index.blade.php`
- Modify: `resources/css/app.css`
- Test: `tests/Feature/Projects/CreativeAssetTest.php`
- Test: `tests/Feature/Projects/SharedMaterialsTest.php`

**Interfaces:** Consumes creative storage fields and `LandingPage.detail_image_path`. Produces a download response only for a stored file; otherwise exposes a source URL.

- [ ] **Step 1: Write failing tests**

```php
Storage::fake('local');
Storage::disk('local')->put('creative-assets/1/video.mp4', 'video');
$this->actingAs($viewer)->get("/projects/{$project->id}/creative-assets/{$asset->id}/download")
    ->assertOk()->assertHeader('content-disposition');
```

```php
$this->actingAs($viewer)->get("/projects?stage=content_creative&project={$project->id}")
    ->assertSee('详情页预览')->assertSee($landingPage->page_url, false);
```

- [ ] **Step 2: Run tests and confirm RED**

Run: `docker exec erp-redesign-live php artisan test tests/Feature/Projects/CreativeAssetTest.php tests/Feature/Projects/SharedMaterialsTest.php --compact`

Expected: FAIL because the shared card lacks preview and the download state is not guarded by file existence.

- [ ] **Step 3: Implement file-state handling and layout**

```php
abort_unless($asset->storage_path && $asset->storage_disk && Storage::disk($asset->storage_disk)->exists($asset->storage_path), 404);
return Storage::disk($asset->storage_disk)->download($asset->storage_path, basename($asset->storage_path));
```

Use `打开参考链接` for external-only assets. Render landing-page `detail_image_path` with `object-contain`. Apply `overflow-wrap:anywhere` to resource links and use normal anchor tags for every URL.

- [ ] **Step 4: Run focused tests and confirm GREEN**

Run: `docker exec erp-redesign-live php artisan test tests/Feature/Projects/CreativeAssetTest.php tests/Feature/Projects/SharedMaterialsTest.php --compact`

Expected: PASS.

- [ ] **Step 5: Commit the task**

Run: `git add app/Http/Controllers/CreativeAssetController.php resources/views/projects/index.blade.php resources/css/app.css tests/Feature/Projects/CreativeAssetTest.php tests/Feature/Projects/SharedMaterialsTest.php && git commit -m "fix: improve shared materials downloads and previews"`

### Task 5: 多项目筛选、刷新提示与固定项目头部

**Files:**
- Modify: `app/Http/Controllers/ProductProjectController.php`
- Modify: `resources/views/projects/index.blade.php`
- Modify: `resources/css/app.css`
- Test: `tests/Feature/Projects/ProductProjectFilterTest.php`
- Test: `tests/Feature/Projects/ProductCenterTest.php`

**Interfaces:** Consumes filtered `$projects`; produces every matching project card, a `data-workspace-refresh` control and responsive sticky header CSS.

- [ ] **Step 1: Write failing tests**

```php
$this->actingAs($user)->get('/projects?stage=market_research&category=灯具')
    ->assertOk()->assertSee('匹配的产品项目')
    ->assertSee('灯具项目 A')->assertSee('灯具项目 B')->assertDontSee('非灯具项目');
```

```php
$this->actingAs($user)->get('/projects?stage=market_research')
    ->assertSee('刷新最新内容')->assertSee('data-workspace-refresh', false)
    ->assertSee('project-summary-header', false);
```

- [ ] **Step 2: Run tests and confirm RED**

Run: `docker exec erp-redesign-live php artisan test tests/Feature/Projects/ProductProjectFilterTest.php tests/Feature/Projects/ProductCenterTest.php --compact`

Expected: FAIL because results currently expose only the selected project and no refresh control exists.

- [ ] **Step 3: Implement list, refresh state and responsive sticky behavior**

```blade
@foreach($projects as $project)
  <a href="{{ route('projects.index', [...$filters, 'project' => $project->id]) }}">{{ $project->product_name }}</a>
@endforeach
<button type="button" data-workspace-refresh>刷新最新内容</button>
```

Use stage-keyed `localStorage` for last-view timestamps and compare displayed resource timestamps after reload. At `min-width: 901px`, apply `position: sticky; top: .75rem; z-index: 20` to the project summary header; remove sticky positioning on narrow screens.

- [ ] **Step 4: Run focused tests and confirm GREEN**

Run: `docker exec erp-redesign-live php artisan test tests/Feature/Projects/ProductProjectFilterTest.php tests/Feature/Projects/ProductCenterTest.php --compact`

Expected: PASS.

- [ ] **Step 5: Commit the task**

Run: `git add app/Http/Controllers/ProductProjectController.php resources/views/projects/index.blade.php resources/css/app.css tests/Feature/Projects/ProductProjectFilterTest.php tests/Feature/Projects/ProductCenterTest.php && git commit -m "feat: improve project discovery and refresh"`

### Task 6: 全量验证、构建、部署与推送

**Files:**
- Modify: only files changed by Tasks 1–5.
- Test: complete Laravel suite.

**Interfaces:** Consumes all completed features; produces built Vite manifest/assets and a production deployment.

- [ ] **Step 1: Run complete application verification**

Run: `docker exec erp-redesign-live php artisan test --compact`

Expected: zero failed tests.

- [ ] **Step 2: Build and compile views**

Run: `npm run build; docker exec erp-redesign-live php artisan view:clear; docker exec erp-redesign-live php artisan view:cache`

Expected: Vite succeeds and Blade reports no compilation errors.

- [ ] **Step 3: Deploy production source and assets**

Copy changed source to `/home/customer/www/aitoolgroup.com`, copy full Vite manifest/assets to both `public/build` and `public_html/build`, then run `php artisan optimize` remotely.

- [ ] **Step 4: Verify production resources**

Run: `curl.exe -sS -I --max-time 25 https://aitoolgroup.com/login`

Expected: `HTTP/1.1 200 OK`; verify the versioned CSS asset from the current manifest also returns `200 OK`.

- [ ] **Step 5: Push the release branch**

Run: `git push origin redesign-phase-one`
