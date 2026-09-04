# ERP MVP Core Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Deliver the first deployable ERP slice: authenticated users can create a product project, research it, add supplier/SKU information, route work to departments, and retain an audit trail.

**Architecture:** Build a Laravel application with a MySQL relational model and a server-rendered Inertia/React user interface. The first slice concentrates all workflow behavior behind project, task, and transition services. Object storage and campaign metrics are represented by stable interfaces now, then enabled in later slices.

**Tech Stack:** PHP 8.4.1+, Laravel 12, MySQL 8, Inertia.js, React, TypeScript, Tailwind CSS, PHPUnit/Pest, Laravel policies, Laravel filesystem S3 driver.

**Spec:** `docs/superpowers/specs/2026-09-04-cross-border-product-erp-design.md`

## Global Constraints

- Deploy a PHP/Laravel application to SiteGround Cloud and MySQL; do not require a permanently running Node process in production.
- Store uploaded asset bytes in a private Cloudflare R2 bucket via S3-compatible APIs; store only metadata and object keys in MySQL.
- Keep manual create/edit/upload/link entry available for every first-release business record.
- Enforce the four department boundaries with Laravel policies and retain a user/time audit history for project workflow events.
- Use Chinese labels in the user interface and immutable English identifiers in code and database schema.

---

## File Structure

- `app/Models/Department.php`: department record and members relation.
- `app/Models/User.php`: user profile, department relation, role helpers.
- `app/Models/ProductProject.php`: product-project aggregate and lifecycle state.
- `app/Models/ResearchSource.php`: market-research evidence and source URL.
- `app/Models/ProductSource.php`: 1688 or other supplier source.
- `app/Models/ProductSku.php`: internal SKU lifecycle and variant details.
- `app/Models/WorkflowTransition.php`: immutable submit, accept, and return events.
- `app/Models/Task.php`: cross-department actionable work.
- `app/Actions/Projects/CreateProductProject.php`: validated project creation.
- `app/Actions/Projects/SubmitProjectStage.php`: stage hand-off with task creation and audit event.
- `app/Policies/ProductProjectPolicy.php`: department-aware view and update authorization.
- `app/Http/Controllers/ProductProjectController.php`: product project list/create/detail HTTP endpoints.
- `app/Http/Controllers/ResearchSourceController.php`: research source create/update endpoints.
- `app/Http/Controllers/ProductSourceController.php`: supplier source and SKU endpoints.
- `resources/js/Pages/Projects/Index.tsx`: filtered product-project list.
- `resources/js/Pages/Projects/Create.tsx`: manual project entry form.
- `resources/js/Pages/Projects/Show.tsx`: project overview, research, source, SKU, and activity tabs.
- `database/migrations/*`: department, role, project, source, SKU, task, and workflow schema.
- `database/seeders/DepartmentSeeder.php`: the four approved departments.
- `tests/Feature/Projects/*`: browser-facing workflow and authorization tests.
- `tests/Unit/Actions/*`: action-service state transition tests.

## Task 1: Bootstrap the isolated Laravel application

**Files:**
- Create: `erp-workflow/composer.json`
- Create: `erp-workflow/.env.example`
- Create: `erp-workflow/routes/web.php`
- Create: `erp-workflow/tests/Feature/HealthCheckTest.php`

**Interfaces:**
- Produces a Laravel application that answers `GET /` with an authenticated application shell or a login route.

- [ ] **Step 1: Write the failing health-check test**

```php
it('returns a successful response for the application entry point', function () {
    $this->get('/')->assertOk();
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test tests/Feature/HealthCheckTest.php`

Expected: FAIL because the Laravel application does not yet exist.

- [ ] **Step 3: Create the Laravel 12 application and configure the local MySQL connection variables**

```dotenv
APP_NAME="跨境产品 ERP"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=product_erp
DB_USERNAME=product_erp
DB_PASSWORD=
```

- [ ] **Step 4: Add a root route and make the test pass**

```php
Route::get('/', fn () => response()->view('welcome'));
```

- [ ] **Step 5: Run the test and commit**

Run: `php artisan test tests/Feature/HealthCheckTest.php`

Expected: PASS.

Commit: `git add . && git commit -m "chore: bootstrap product erp application"`

## Task 2: Add users, departments, and role authorization

**Files:**
- Create: `database/migrations/2026_09_04_000001_create_departments_table.php`
- Modify: `app/Models/User.php`
- Create: `app/Models/Department.php`
- Create: `database/seeders/DepartmentSeeder.php`
- Create: `tests/Feature/Authorization/DepartmentAccessTest.php`

**Interfaces:**
- Consumes: Laravel user model from Task 1.
- Produces: `User::department(): BelongsTo`, `User::hasRole(string): bool`, and seeded department codes `market_research`, `website_operations`, `content_creative`, `traffic_growth`.

- [ ] **Step 1: Write the failing role test**

```php
it('recognizes a user department and role', function () {
    $department = Department::factory()->create(['code' => 'market_research']);
    $user = User::factory()->create(['department_id' => $department->id, 'role' => 'member']);

    expect($user->department->code)->toBe('market_research')
        ->and($user->hasRole('member'))->toBeTrue()
        ->and($user->hasRole('administrator'))->toBeFalse();
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test tests/Feature/Authorization/DepartmentAccessTest.php`

Expected: FAIL because department fields and helpers are missing.

- [ ] **Step 3: Create the migration, model relationships, role helper, factory state, and four department seeds**

```php
Schema::create('departments', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('code')->unique();
    $table->foreignId('manager_user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

- [ ] **Step 4: Run the focused test and seed test database**

Run: `php artisan test tests/Feature/Authorization/DepartmentAccessTest.php`

Expected: PASS.

- [ ] **Step 5: Commit**

Commit: `git add app database tests && git commit -m "feat: add department roles"`

## Task 3: Implement the product-project aggregate and list/create views

**Files:**
- Create: `database/migrations/2026_09_04_000002_create_product_projects_table.php`
- Create: `app/Models/ProductProject.php`
- Create: `app/Actions/Projects/CreateProductProject.php`
- Create: `app/Http/Requests/StoreProductProjectRequest.php`
- Create: `app/Http/Controllers/ProductProjectController.php`
- Create: `app/Policies/ProductProjectPolicy.php`
- Modify: `routes/web.php`
- Create: `resources/js/Pages/Projects/Index.tsx`
- Create: `resources/js/Pages/Projects/Create.tsx`
- Create: `tests/Feature/Projects/CreateProductProjectTest.php`

**Interfaces:**
- Consumes: authenticated `User`, department membership from Task 2.
- Produces: `CreateProductProject::handle(User $actor, array $data): ProductProject` and routes `projects.index`, `projects.create`, `projects.store`, `projects.show`.

- [ ] **Step 1: Write the failing product creation test**

```php
it('lets a market researcher create a draft project', function () {
    $user = marketResearchUser();

    $this->actingAs($user)->post(route('projects.store'), [
        'product_name' => '星空投影灯',
        'category' => '家居装饰',
        'market' => 'US',
        'priority' => 'high',
    ])->assertRedirect();

    $this->assertDatabaseHas('product_projects', [
        'product_name' => '星空投影灯',
        'current_stage' => 'market_research',
        'status' => 'draft',
        'owner_user_id' => $user->id,
    ]);
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test tests/Feature/Projects/CreateProductProjectTest.php`

Expected: FAIL because the route, request, table, and action do not exist.

- [ ] **Step 3: Implement the table, action, policy, controller, routes, and Chinese-language list/create forms**

```php
enum ProjectStage: string {
    case MarketResearch = 'market_research';
    case WebsiteOperations = 'website_operations';
    case ContentCreative = 'content_creative';
    case TrafficGrowth = 'traffic_growth';
}
```

- [ ] **Step 4: Run focused feature tests**

Run: `php artisan test tests/Feature/Projects/CreateProductProjectTest.php`

Expected: PASS.

- [ ] **Step 5: Commit**

Commit: `git add app database resources routes tests && git commit -m "feat: add product project workspace"`

## Task 4: Add market research evidence and stage submission

**Files:**
- Create: `database/migrations/2026_09_04_000003_create_research_sources_table.php`
- Create: `app/Models/ResearchSource.php`
- Create: `app/Http/Controllers/ResearchSourceController.php`
- Create: `app/Actions/Projects/SubmitProjectStage.php`
- Create: `app/Models/WorkflowTransition.php`
- Create: `database/migrations/2026_09_04_000004_create_workflow_transitions_table.php`
- Modify: `app/Http/Controllers/ProductProjectController.php`
- Modify: `resources/js/Pages/Projects/Show.tsx`
- Create: `tests/Feature/Projects/ResearchSubmissionTest.php`

**Interfaces:**
- Consumes: `ProductProject` from Task 3.
- Produces: `SubmitProjectStage::handle(ProductProject $project, User $actor, ProjectStage $target, string $note): WorkflowTransition`.

- [ ] **Step 1: Write the failing submission test**

```php
it('requires research evidence before submitting to website operations', function () {
    $project = ProductProject::factory()->marketResearch()->create();
    $actor = $project->owner;

    $this->actingAs($actor)->post(route('projects.submit', $project), [
        'target_stage' => 'website_operations',
        'note' => '请确认货源与 SKU。',
    ])->assertSessionHasErrors('research_sources');
});
```

- [ ] **Step 2: Run it to verify it fails**

Run: `php artisan test tests/Feature/Projects/ResearchSubmissionTest.php`

Expected: FAIL because research evidence and submit route are absent.

- [ ] **Step 3: Implement evidence records and submit validation**

```php
Schema::create('research_sources', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_project_id')->constrained()->cascadeOnDelete();
    $table->string('platform');
    $table->string('url', 2048);
    $table->text('evidence_note')->nullable();
    $table->timestamp('captured_at')->nullable();
    $table->foreignId('created_by')->constrained('users');
    $table->timestamps();
});
```

- [ ] **Step 4: Run tests for blocked and successful submission**

Run: `php artisan test tests/Feature/Projects/ResearchSubmissionTest.php`

Expected: PASS for both a missing-evidence rejection and a successful submit after creating a source.

- [ ] **Step 5: Commit**

Commit: `git add app database resources tests && git commit -m "feat: add research evidence workflow"`

## Task 5: Add 1688 sources and the internal SKU lifecycle

**Files:**
- Create: `database/migrations/2026_09_04_000005_create_product_sources_table.php`
- Create: `database/migrations/2026_09_04_000006_create_product_skus_table.php`
- Create: `app/Models/ProductSource.php`
- Create: `app/Models/ProductSku.php`
- Create: `app/Http/Controllers/ProductSourceController.php`
- Modify: `resources/js/Pages/Projects/Show.tsx`
- Create: `tests/Feature/Projects/ProductSourceAndSkuTest.php`

**Interfaces:**
- Consumes: `ProductProject` from Task 3.
- Produces: a `ProductSource` with `supplier_url`, price, weight, and multiple `ProductSku` records with `sku_status` values `proposed`, `pending_creation`, `created`, `imported`, `used_on_page`, `inactive`.

- [ ] **Step 1: Write the failing source and SKU test**

```php
it('lets website operations add an 1688 source and import an internal SKU', function () {
    $project = ProductProject::factory()->websiteOperations()->create();
    $user = websiteOperationsUser();

    $this->actingAs($user)->post(route('projects.sources.store', $project), [
        'supplier_url' => 'https://detail.1688.com/offer/1073153738003.html',
        'purchase_price' => 22.00,
        'currency' => 'CNY',
        'weight_g' => 93,
        'skus' => [['sku_code' => 'NC03342609026143', 'variant_name' => '夜灯+3影片']],
    ])->assertRedirect();

    $this->assertDatabaseHas('product_skus', ['sku_code' => 'NC03342609026143', 'sku_status' => 'imported']);
});
```

- [ ] **Step 2: Run it to verify it fails**

Run: `php artisan test tests/Feature/Projects/ProductSourceAndSkuTest.php`

Expected: FAIL because source and SKU persistence do not exist.

- [ ] **Step 3: Implement the migrations, models, validation, controller, and project-detail source/SKU form**

```php
$table->enum('sku_status', ['proposed', 'pending_creation', 'created', 'imported', 'used_on_page', 'inactive'])
    ->default('proposed');
```

- [ ] **Step 4: Run the focused test**

Run: `php artisan test tests/Feature/Projects/ProductSourceAndSkuTest.php`

Expected: PASS.

- [ ] **Step 5: Commit**

Commit: `git add app database resources tests && git commit -m "feat: add supplier sources and sku lifecycle"`

## Task 6: Verify the core slice and prepare the next slice

**Files:**
- Modify: `README.md`
- Create: `tests/Feature/Projects/EndToEndCoreWorkflowTest.php`

**Interfaces:**
- Consumes: Tasks 1-5.
- Produces: a repeatable end-to-end acceptance test from research project to website-operations source and SKU record.

- [ ] **Step 1: Write the failing end-to-end scenario**

```php
it('moves an evidenced product from market research to website operations with an imported SKU', function () {
    $researcher = marketResearchUser();
    $project = CreateProductProject::handle($researcher, [
        'product_name' => '星空投影灯', 'category' => '家居装饰', 'market' => 'US', 'priority' => 'high',
    ]);

    ResearchSource::factory()->for($project)->create(['platform' => 'TikTok']);
    SubmitProjectStage::handle($project, $researcher, ProjectStage::WebsiteOperations, '请确认货源。');

    expect($project->refresh()->current_stage)->toBe('website_operations');
});
```

- [ ] **Step 2: Run the test to identify integration gaps**

Run: `php artisan test tests/Feature/Projects/EndToEndCoreWorkflowTest.php`

Expected: FAIL until all action interfaces and test factories align.

- [ ] **Step 3: Fix only interface mismatches and document the local setup plus SiteGround/R2 deployment assumptions**

```markdown
## Production services

- Web and MySQL: SiteGround Cloud
- Asset bytes: private Cloudflare R2 bucket
- Asset metadata: MySQL
- Background sync: later slice; no automatic crawler in the core release
```

- [ ] **Step 4: Run the complete test suite**

Run: `php artisan test`

Expected: PASS with no failures.

- [ ] **Step 5: Commit**

Commit: `git add README.md tests && git commit -m "test: cover core product workflow"`

## Self-review

- Spec coverage: Tasks 1-6 implement authenticated department access, manual product/research/source/SKU entry, source provenance, department hand-off, audit records, and the first browser UI. Creative assets, landing pages, campaign tests, R2 presigned uploads, and notifications deliberately remain in later independent slices.
- Placeholder scan: no deferred implementation language appears inside a task step; deferred modules are declared only in scope boundaries.
- Type consistency: project stage strings use `market_research`, `website_operations`, `content_creative`, and `traffic_growth` everywhere; `CreateProductProject::handle` and `SubmitProjectStage::handle` have one declared signature each.
