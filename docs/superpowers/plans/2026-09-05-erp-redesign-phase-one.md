# Cross-Border ERP Redesign Phase One Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the prototype-style Laravel screens with a coherent, SiteGround-ready ERP workspace where the four departments collaborate inside one product project.

**Architecture:** Keep Laravel 12 and MySQL, but introduce a reusable Blade application shell and focused query/action classes. Preserve the existing product records and migration history; add non-destructive schema extensions and use a project workspace view composed from independently rendered tab partials. All business writes go through actions that add an immutable activity record.

**Tech Stack:** PHP 8.4+, Laravel 12, Blade, Tailwind CSS built assets, Alpine.js, MySQL 8, PHPUnit, Laravel policies, Laravel filesystem S3 driver.

**Spec:** `docs/superpowers/specs/2026-09-05-cross-border-erp-redesign.md`

## Global Constraints

- Deploy to SiteGround Cloud with Laravel `public` as the web root; production cannot require a long-running Node process.
- Use Cloudflare R2 private storage and signed URLs for all production asset bytes; MySQL only stores metadata and object keys.
- Every business record keeps a manual create/edit/upload/link workflow, even after later import/API work is added.
- Keep current migrations and existing records; new migrations may only add tables/columns/indexes, never drop production tables.
- Use Chinese UI copy and stable English identifiers in routes, models, schemas, and enums.
- Every stage handoff, feedback state change, and core record mutation must create an activity event.
- Tests run with `php artisan test`; each feature starts with a focused failing test.

## File Structure

- `resources/views/layouts/app.blade.php`: application shell, navigation, user menu, shared flash/error UI.
- `resources/css/app.css`: Tailwind entry point plus ERP design tokens and small component layers.
- `resources/views/components/*`: reusable badge, metric-card, stage-rail, empty-state, and activity-item components.
- `app/Support/ProjectStage.php`: canonical stage labels, department code mapping, and ordered stage list.
- `app/Models/ProjectActivity.php`: immutable business activity event.
- `app/Actions/Activity/RecordProjectActivity.php`: one write interface for audit/activity events.
- `app/Queries/DashboardQuery.php`: department-scoped dashboard data.
- `app/Queries/ProjectWorkspaceQuery.php`: fully loaded project workspace data.
- `app/Http/Controllers/DashboardController.php`: redesigned dashboard response.
- `app/Http/Controllers/ProductProjectController.php`: product-center list/create/show endpoints.
- `app/Http/Controllers/ProjectWorkspaceController.php`: project overview and tab routing.
- `app/Actions/Projects/AdvanceProjectStage.php`: validated handoff with activity recording.
- `app/Policies/ProductProjectPolicy.php`: department-aware project access/updates.
- `resources/views/dashboard/index.blade.php`: work dashboard.
- `resources/views/projects/index.blade.php`: product center list and filters.
- `resources/views/projects/workspace.blade.php`: project frame and tab navigation.
- `resources/views/projects/tabs/*.blade.php`: overview, research, operations, assets, campaigns, and feedback tab content.
- `database/migrations/2026_09_05_000014_create_project_activities_table.php`: append-only activity data.
- `database/migrations/2026_09_05_000015_create_project_members_table.php`: project participants and role context.
- `database/migrations/2026_09_05_000016_create_project_decisions_table.php`: explicit SKU/price/spec decision queue.
- `tests/Feature/Layout/ApplicationShellTest.php`: navigation and role-aware shell behavior.
- `tests/Feature/Dashboard/DashboardWorkspaceTest.php`: department and administrator dashboards.
- `tests/Feature/Projects/ProductCenterTest.php`: filters and creation journey.
- `tests/Feature/Projects/ProjectWorkspaceTest.php`: rendered project workbench and tab data.
- `tests/Feature/Projects/StageHandoffTest.php`: handoff rules and activity records.
- `tests/Feature/Projects/ProjectFeedbackCenterTest.php`: feedback assignment, reply, and resolution history.

## Task 1: Establish the ERP application shell and design system

**Files:**
- Create: `resources/views/layouts/app.blade.php`
- Create: `resources/views/components/nav-item.blade.php`
- Create: `resources/views/components/status-badge.blade.php`
- Create: `resources/views/components/metric-card.blade.php`
- Modify: `resources/css/app.css`
- Modify: `resources/views/auth/login.blade.php`
- Modify: `resources/views/projects/index.blade.php`
- Test: `tests/Feature/Layout/ApplicationShellTest.php`

**Interfaces:**
- Produces `<x-nav-item route="dashboard" label="工作台" />`, `<x-status-badge status="in_progress" />`, and `<x-metric-card label="待处理反馈" value="4" hint="2 项今日到期" />`.
- Consumes the authenticated `User` model and `User::hasRole('administrator')`.

- [ ] **Step 1: Write the failing shell test**

```php
public function test_an_authenticated_member_sees_the_erp_navigation_shell(): void
{
    $user = User::factory()->create();

    $this->actingAs($user)->get('/projects')
        ->assertOk()
        ->assertSee('工作台')
        ->assertSee('产品中心')
        ->assertSee('素材中心')
        ->assertSee('投放中心')
        ->assertSee('反馈中心');
}
```

- [ ] **Step 2: Run the focused test and verify it fails because the current page has no ERP shell**

Run: `php artisan test tests/Feature/Layout/ApplicationShellTest.php`

Expected: FAIL on the missing navigation labels.

- [ ] **Step 3: Build the shell and reusable components**

Use one `layouts.app` Blade layout. Include the fixed desktop sidebar, top context bar, named content slot, flash-message region, and a logout form. Keep all category colors and spacing tokens in `resources/css/app.css`; views must not add page-wide inline style blocks.

```blade
<x-nav-item route="dashboard" label="工作台" icon="dashboard" />
<x-nav-item route="projects.index" label="产品中心" icon="products" />
<x-nav-item route="assets.index" label="素材中心" icon="assets" />
<x-nav-item route="campaigns.index" label="投放中心" icon="campaigns" />
<x-nav-item route="feedback.index" label="反馈中心" icon="feedback" :count="$openFeedbackCount" />
```

- [ ] **Step 4: Convert login and project pages to the shell**

The login page remains standalone. Authenticated pages extend `layouts.app`; admin-only items use `@if (auth()->user()->hasRole('administrator'))`.

- [ ] **Step 5: Run the focused test and front-end build**

Run: `php artisan test tests/Feature/Layout/ApplicationShellTest.php && npm run build`

Expected: PASS and Vite emits static production assets.

- [ ] **Step 6: Commit**

```bash
git add resources tests/Feature/Layout/ApplicationShellTest.php
git commit -m "feat: add erp application shell"
```

## Task 2: Add canonical stages, project activity, members, and decisions

**Files:**
- Create: `app/Support/ProjectStage.php`
- Create: `app/Models/ProjectActivity.php`
- Create: `app/Models/ProjectMember.php`
- Create: `app/Models/ProjectDecision.php`
- Create: `app/Actions/Activity/RecordProjectActivity.php`
- Create: `database/migrations/2026_09_05_000014_create_project_activities_table.php`
- Create: `database/migrations/2026_09_05_000015_create_project_members_table.php`
- Create: `database/migrations/2026_09_05_000016_create_project_decisions_table.php`
- Create: `database/factories/ProductProjectFactory.php`
- Create: `database/factories/ProductSourceFactory.php`
- Create: `database/factories/ProductSkuFactory.php`
- Create: `database/factories/OptimizationFeedbackFactory.php`
- Create: `database/factories/ProjectActivityFactory.php`
- Create: `database/factories/ProjectDecisionFactory.php`
- Modify: `app/Models/ProductProject.php`
- Test: `tests/Feature/Projects/ProjectActivityTest.php`

**Interfaces:**
- Produces `ProjectStage::ordered(): array`, `ProjectStage::label(string $stage): string`, and `RecordProjectActivity::handle(ProductProject $project, User $actor, string $event, array $payload = []): ProjectActivity`.
- `ProjectActivity` has `product_project_id`, `actor_id`, `event`, `subject_type`, `subject_id`, `payload`, and timestamps.

- [ ] **Step 1: Write the failing activity test**

```php
public function test_recording_a_project_event_creates_an_immutable_activity_item(): void
{
    [$user, $project] = $this->projectForDepartment('website_operations');

    app(RecordProjectActivity::class)->handle($project, $user, 'sku.created', [
        'sku_code' => 'NC03342609026143',
    ]);

    $this->assertDatabaseHas('project_activities', [
        'product_project_id' => $project->id,
        'actor_id' => $user->id,
        'event' => 'sku.created',
    ]);
}
```

- [ ] **Step 2: Run it and verify the missing class/table failure**

Run: `php artisan test tests/Feature/Projects/ProjectActivityTest.php`

Expected: FAIL because `RecordProjectActivity` and `project_activities` do not exist.

- [ ] **Step 3: Add non-destructive migrations and models**

Create append-only activity storage. `project_members` contains project, user, department, and assignment role. `project_decisions` contains project, decision type (`sku`, `pricing`, `specification`, `landing_page`), title, status (`open`, `confirmed`, `rejected`), details, and creator. Cast JSON payload/detail fields to arrays. Add factories for projects, sources, SKUs, feedback, activities, and decisions; every factory must create valid parent records with its `for()` relationship rather than hard-coding foreign IDs.

- [ ] **Step 4: Add project relations and the activity action**

```php
public function activities(): HasMany
{
    return $this->hasMany(ProjectActivity::class)->latest();
}
```

The action uses `ProjectActivity::create()` and never exposes update/delete methods.

- [ ] **Step 5: Run the focused test**

Run: `php artisan test tests/Feature/Projects/ProjectActivityTest.php`

Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app database tests/Feature/Projects/ProjectActivityTest.php
git commit -m "feat: add project activity foundation"
```

## Task 3: Rebuild the department-scoped work dashboard

**Files:**
- Create: `app/Queries/DashboardQuery.php`
- Modify: `app/Http/Controllers/DashboardController.php`
- Modify: `resources/views/dashboard/index.blade.php`
- Create: `resources/views/components/stage-rail.blade.php`
- Test: `tests/Feature/Dashboard/DashboardWorkspaceTest.php`

**Interfaces:**
- Produces `DashboardQuery::for(User $user): array{projects: Collection, feedback: Collection, metrics: object, activities: Collection}`.
- Department users receive projects whose current stage matches their department; administrators receive all projects.

- [ ] **Step 1: Write a failing department dashboard test**

```php
public function test_a_department_dashboard_excludes_projects_owned_by_other_stages(): void
{
    $websiteUser = $this->userForDepartment('website_operations');
    ProductProject::factory()->create(['product_name' => '页面待处理产品', 'current_stage' => 'website_operations']);
    ProductProject::factory()->create(['product_name' => '素材待处理产品', 'current_stage' => 'content_creative']);

    $this->actingAs($websiteUser)->get('/dashboard')
        ->assertOk()
        ->assertSee('页面待处理产品')
        ->assertDontSee('素材待处理产品');
}
```

- [ ] **Step 2: Run the focused test and verify it fails against the current unscoped dashboard query**

Run: `php artisan test tests/Feature/Dashboard/DashboardWorkspaceTest.php`

Expected: FAIL because the dashboard exposes both projects or lacks the redesigned workspace data.

- [ ] **Step 3: Implement `DashboardQuery`**

Load stage-scoped projects with the owner, latest activity, unresolved feedback, and current progress counts. Aggregate campaign spend, impressions, clicks, conversions, and calculate CTR as `clicks / impressions * 100`, returning zero when impressions are zero.

- [ ] **Step 4: Render dashboard cards, project progress, feedback, and activity**

Use the shared metric-card and stage-rail components. Show empty states when no projects or feedback exist. Do not query models directly from Blade templates.

- [ ] **Step 5: Run focused dashboard tests**

Run: `php artisan test tests/Feature/Dashboard/DashboardWorkspaceTest.php`

Expected: PASS for department filtering and administrator global view.

- [ ] **Step 6: Commit**

```bash
git add app resources tests/Feature/Dashboard/DashboardWorkspaceTest.php
git commit -m "feat: rebuild department dashboard"
```

## Task 4: Rebuild product center with filters and project creation

**Files:**
- Create: `app/Http/Requests/FilterProductProjectsRequest.php`
- Modify: `app/Http/Controllers/ProductProjectController.php`
- Modify: `resources/views/projects/index.blade.php`
- Create: `resources/views/projects/partials/project-row.blade.php`
- Test: `tests/Feature/Projects/ProductCenterTest.php`

**Interfaces:**
- Consumes `stage`, `status`, `market`, `priority`, and `search` query parameters.
- Produces `projects.index` with filter state preserved and a compact project list linked to `projects.workspace`.

- [ ] **Step 1: Write a failing product-center filter test**

```php
public function test_product_center_filters_by_stage_and_search_term(): void
{
    $user = $this->userForDepartment('market_research');
    ProductProject::factory()->create(['product_name' => '星空投影灯', 'current_stage' => 'market_research']);
    ProductProject::factory()->create(['product_name' => '旅行收纳包', 'current_stage' => 'website_operations']);

    $this->actingAs($user)->get('/projects?stage=market_research&search=投影')
        ->assertOk()
        ->assertSee('星空投影灯')
        ->assertDontSee('旅行收纳包');
}
```

- [ ] **Step 2: Run the focused test and verify it fails**

Run: `php artisan test tests/Feature/Projects/ProductCenterTest.php`

Expected: FAIL because the current list ignores filters.

- [ ] **Step 3: Add query validation and filtering**

Allow only known stage/status/priority values. Apply search to `product_name` and `project_code`; preserve filters in pagination links. Keep project creation restricted to market research members and administrators.

- [ ] **Step 4: Render filters and the product list**

Show project code, product name, target market, priority, current stage, health indicator, latest activity time, and a direct workspace link.

- [ ] **Step 5: Run the focused test**

Run: `php artisan test tests/Feature/Projects/ProductCenterTest.php`

Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app resources tests/Feature/Projects/ProductCenterTest.php
git commit -m "feat: rebuild product center"
```

## Task 5: Build the project workspace overview and stage handoff

**Files:**
- Create: `app/Queries/ProjectWorkspaceQuery.php`
- Create: `app/Actions/Projects/AdvanceProjectStage.php`
- Create: `app/Http/Controllers/ProjectWorkspaceController.php`
- Create: `resources/views/projects/workspace.blade.php`
- Create: `resources/views/projects/tabs/overview.blade.php`
- Modify: `routes/web.php`
- Modify: `app/Policies/ProductProjectPolicy.php`
- Test: `tests/Feature/Projects/ProjectWorkspaceTest.php`
- Test: `tests/Feature/Projects/StageHandoffTest.php`

**Interfaces:**
- Produces `ProjectWorkspaceQuery::for(ProductProject $project): array` with project, members, decisions, deliverable counts, activities, and health status.
- Produces `AdvanceProjectStage::handle(ProductProject $project, User $actor, string $targetStage, string $note): WorkflowTransition`.

- [ ] **Step 1: Write the failing workspace rendering test**

```php
public function test_project_workspace_shows_stage_rail_decisions_and_recent_activity(): void
{
    [$user, $project] = $this->projectForDepartment('website_operations');
    ProjectDecision::factory()->for($project)->create(['title' => '确认夜灯 SKU 组合', 'status' => 'open']);
    app(RecordProjectActivity::class)->handle($project, $user, 'source.created');

    $this->actingAs($user)->get(route('projects.workspace', $project))
        ->assertOk()
        ->assertSee('阶段推进')
        ->assertSee('确认夜灯 SKU 组合')
        ->assertSee('最近活动');
}
```

- [ ] **Step 2: Run the test and verify the route/view failure**

Run: `php artisan test tests/Feature/Projects/ProjectWorkspaceTest.php`

Expected: FAIL because `projects.workspace` is absent.

- [ ] **Step 3: Implement the workspace query, route, controller, and overview view**

The view has a fixed project header, status/market/priority chips, six tab links, stage rail, decision queue, delivery summary, health label, participant list, and activity sidebar. All data comes from `ProjectWorkspaceQuery`.

- [ ] **Step 4: Write the failing handoff test**

```php
public function test_valid_handoff_updates_stage_and_records_transition_and_activity(): void
{
    [$researcher, $project] = $this->projectForDepartment('market_research');
    ResearchSource::factory()->for($project)->create();

    app(AdvanceProjectStage::class)->handle($project, $researcher, 'website_operations', '请确认货源与 SKU。');

    $this->assertDatabaseHas('workflow_transitions', ['product_project_id' => $project->id, 'to_stage' => 'website_operations']);
    $this->assertDatabaseHas('project_activities', ['product_project_id' => $project->id, 'event' => 'stage.advanced']);
}
```

- [ ] **Step 5: Run the handoff test and verify it fails because activity is not recorded by the existing action**

Run: `php artisan test tests/Feature/Projects/StageHandoffTest.php`

Expected: FAIL on the missing `stage.advanced` activity.

- [ ] **Step 6: Implement handoff rules in `AdvanceProjectStage`**

Validate stage order, source evidence for research handoff, supplier/SKU/page prerequisites for website operations, approved asset prerequisite for content creative, and metric/conclusion prerequisite for traffic growth. Create the workflow transition and `stage.advanced` activity inside one database transaction.

- [ ] **Step 7: Run workspace and handoff tests**

Run: `php artisan test tests/Feature/Projects/ProjectWorkspaceTest.php tests/Feature/Projects/StageHandoffTest.php`

Expected: PASS.

- [ ] **Step 8: Commit**

```bash
git add app resources routes tests/Feature/Projects
git commit -m "feat: add project collaboration workspace"
```

## Task 6: Fill the operational workspace tabs and record activity

**Files:**
- Create: `resources/views/projects/tabs/research-sources.blade.php`
- Create: `resources/views/projects/tabs/operations.blade.php`
- Create: `resources/views/projects/tabs/assets.blade.php`
- Create: `resources/views/projects/tabs/campaigns.blade.php`
- Modify: `app/Http/Controllers/ResearchSourceController.php`
- Modify: `app/Http/Controllers/ProductSourceController.php`
- Modify: `app/Http/Controllers/LandingPageController.php`
- Modify: `app/Http/Controllers/CreativeAssetController.php`
- Modify: `app/Http/Controllers/CampaignTestController.php`
- Modify: `app/Models/ProductSku.php`
- Test: `tests/Feature/Projects/ProjectWorkspaceTabsTest.php`

**Interfaces:**
- Each existing write controller calls `RecordProjectActivity` after a successful write.
- Workspace tabs render current project records and department-appropriate manual forms.

- [ ] **Step 1: Write the failing tab-content test**

```php
public function test_website_operations_tab_shows_supplier_sku_and_landing_page_records(): void
{
    [$user, $project] = $this->projectForDepartment('website_operations');
    $source = ProductSource::factory()->for($project)->create();
    ProductSku::factory()->for($source, 'source')->for($project)->create(['sku_code' => 'NC03342609026143']);

    $this->actingAs($user)->get(route('projects.workspace', ['project' => $project, 'tab' => 'operations']))
        ->assertOk()
        ->assertSee('1688 货源')
        ->assertSee('NC03342609026143')
        ->assertSee('落地页版本');
}
```

- [ ] **Step 2: Run the test and verify it fails because the tab is not rendered**

Run: `php artisan test tests/Feature/Projects/ProjectWorkspaceTabsTest.php`

Expected: FAIL on missing operations tab content.

- [ ] **Step 3: Add tab selection and data loading**

Allow exactly `overview`, `research`, `operations`, `assets`, `campaigns`, and `feedback` values. Eager-load only the relation set required for the selected tab. Render manual create forms only for the authorized department.

- [ ] **Step 4: Add activity recording to existing business writes**

Record events `research_source.created`, `supplier_source.created`, `sku.created`, `landing_page.created`, `creative_asset.created`, and `campaign_test.created` after their record transaction commits. Include the created record ID and human-readable title in event payloads.

- [ ] **Step 5: Run the tab test and existing write tests**

Run: `php artisan test tests/Feature/Projects/ProjectWorkspaceTabsTest.php tests/Feature/Projects/ResearchSubmissionTest.php tests/Feature/Projects/ProductSourceAndSkuTest.php tests/Feature/Projects/LandingPageTest.php tests/Feature/Projects/CreativeAssetTest.php tests/Feature/Projects/CampaignTestTest.php`

Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app resources tests/Feature/Projects
git commit -m "feat: add project workspace delivery tabs"
```

## Task 7: Rebuild feedback center and resolution audit trail

**Files:**
- Create: `app/Http/Controllers/FeedbackCenterController.php`
- Create: `resources/views/feedback/index.blade.php`
- Modify: `app/Http/Controllers/OptimizationFeedbackController.php`
- Modify: `app/Models/OptimizationFeedback.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Projects/ProjectFeedbackCenterTest.php`

**Interfaces:**
- Produces `feedback.index`, filtered by target department for members and unfiltered for administrators.
- Resolution produces `feedback.accepted`, `feedback.in_progress`, or `feedback.resolved` project activity events.

- [ ] **Step 1: Write the failing feedback-center test**

```php
public function test_target_department_only_sees_its_open_feedback_in_feedback_center(): void
{
    $websiteUser = $this->userForDepartment('website_operations');
    $websiteFeedback = OptimizationFeedback::factory()->create(['target_stage' => 'website_operations', 'status' => 'open']);
    OptimizationFeedback::factory()->create(['target_stage' => 'content_creative', 'status' => 'open']);

    $this->actingAs($websiteUser)->get('/feedback')
        ->assertOk()
        ->assertSee($websiteFeedback->note)
        ->assertDontSee('content_creative');
}
```

- [ ] **Step 2: Run it and verify the feedback-center route failure**

Run: `php artisan test tests/Feature/Projects/ProjectFeedbackCenterTest.php`

Expected: FAIL because `/feedback` is absent.

- [ ] **Step 3: Implement feedback center and resolution activity recording**

List open/in-progress feedback with project name, source campaign, linked subject, assignee department, created time, and processing action. Enforce that only target department members or administrators can update. Record an activity after each status transition.

- [ ] **Step 4: Run the feedback-center test and existing resolution test**

Run: `php artisan test tests/Feature/Projects/ProjectFeedbackCenterTest.php tests/Feature/Projects/OptimizationFeedbackTest.php`

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app resources routes tests/Feature/Projects
git commit -m "feat: rebuild feedback center"
```

## Task 8: Verify phase one, document R2 boundary, and prepare SiteGround deployment

**Files:**
- Create: `tests/Feature/Projects/RedesignAcceptanceTest.php`
- Modify: `README.md`
- Create: `docs/deployment/siteground-phase-one.md`

**Interfaces:**
- Produces one acceptance test covering research evidence, operations records, creative asset, campaign result, feedback resolution, and visible activity trail.
- Documents exact production configuration variables without including secrets.

- [ ] **Step 1: Write the failing end-to-end redesign acceptance test**

```php
public function test_a_product_has_a_traceable_path_from_research_to_feedback_resolution(): void
{
    $researcher = $this->userForDepartment('market_research');
    $project = app(CreateProductProject::class)->handle($researcher, [
        'product_name' => '星空投影灯', 'market' => 'US', 'priority' => 'high',
    ]);

    ResearchSource::factory()->for($project)->create();
    app(AdvanceProjectStage::class)->handle($project, $researcher, 'website_operations', '请确认货源。');

    expect($project->activities()->pluck('event'))->toContain('stage.advanced');
}
```

- [ ] **Step 2: Run it and resolve only integration gaps**

Run: `php artisan test tests/Feature/Projects/RedesignAcceptanceTest.php`

Expected: FAIL until action interfaces and activity relationships are aligned.

- [ ] **Step 3: Add the SiteGround/R2 deployment guide**

Document `APP_ENV=production`, `APP_DEBUG=false`, MySQL connection variables, private R2 disk variables, `php artisan migrate --force`, `php artisan config:cache`, scheduler Cron command, writable directories, and the required web root. State that R2 upload implementation belongs to phase two and phase one keeps existing local/demo storage adapter only for development.

- [ ] **Step 4: Run the complete test suite and build**

Run: `php artisan test && npm run build`

Expected: PASS with no failures and static front-end assets generated.

- [ ] **Step 5: Commit**

```bash
git add README.md docs tests app resources
git commit -m "test: verify erp redesign phase one"
```

## Plan Self-Review

- Spec coverage: Tasks 1-7 implement the approved shell, work dashboard, product center, six-tab workspace, roles, handoffs, manual workflows, feedback, and activity history. Task 8 proves the end-to-end path and documents SiteGround/R2 boundaries.
- Intentional phase boundary: R2 signed upload implementation, batch import, external APIs, automatic crawling, scheduled synchronization, notifications, and advanced analytics are phase two/three and are not represented as fake first-phase functionality.
- Data safety: every schema change is additive; the independent worktree preserves `main` while the new experience is built and reviewed.
- Type consistency: stage values are `market_research`, `website_operations`, `content_creative`, and `traffic_growth`; `RecordProjectActivity::handle` and `AdvanceProjectStage::handle` have one declared signature each.
