# SKU、Shopify 与投放反馈工作流修正 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Align the Laravel ERP with the real market-research → internal SKU → Shopify → video → Facebook feedback workflow.

**Architecture:** Keep `product_projects` as the aggregate root. Make internal SKUs independent from supplier records, preserve the existing Shopify-page-to-SKU join, require campaign attribution to a creative asset and landing page, and expand one feedback submission into separate target-department records.

**Tech Stack:** Laravel 12, PHP 8.4, Blade, Tailwind, SQLite test database, MySQL-compatible additive migrations, PHPUnit.

**Spec:** `docs/superpowers/specs/2026-09-05-product-system-sku-shopify-feedback-correction.md`

## Global Constraints

- Use Chinese UI copy and stable English values for schema and route identifiers.
- Preserve manual input, upload and URL-paste entry points for all four departments.
- Keep SiteGround-compatible Laravel server rendering; do not add a required long-running process.
- Every schema change is forward-compatible and preserves existing records.
- Every new core write records a project activity.

---

### Task 1: Make market research the internal SKU owner

**Files:**
- Create: `app/Http/Controllers/ProductSkuController.php`
- Create: `database/migrations/2026_09_05_000018_make_product_sku_source_optional.php`
- Modify: `app/Models/ProductSku.php`
- Modify: `app/Http/Controllers/ProductSourceController.php`
- Modify: `app/Queries/ProjectWorkspaceQuery.php`
- Modify: `resources/views/projects/workspace.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Projects/InternalSkuIntakeTest.php`

**Interfaces:**
- Produces `POST /projects/{project}/skus`, named `projects.skus.store`.
- Accepts `sku_code`, `variant_name`; creates a SKU with a null `product_source_id` and event `sku.imported_from_product_system`.
- `POST /projects/{project}/sources` accepts supplier details only and event `supplier_source.created`.

- [ ] Write a failing feature test where a market research member posts an internal SKU before any source exists, then assert the SKU has a null source, the correct project, and a project activity.
- [ ] Run `docker exec erp-redesign-demo php artisan test --filter=InternalSkuIntakeTest`; confirm it fails because no SKU route permits market research input.
- [ ] Add the nullable source migration and `ProductSkuController`; validate SKU uniqueness within a project and restrict the endpoint to market research or administrators.
- [ ] Remove the nested SKU validation and SKU creation loop from `ProductSourceController`; retain supplier validation, source activity and the operations-tab redirect.
- [ ] Add the market-research “回填内部产品系统 SKU” form to the research tab and retain the website-operations source form separately.
- [ ] Re-run the focused test and commit `feat: let market research import internal sku`.

### Task 2: Make Shopify page setup consume market SKU

**Files:**
- Modify: `resources/views/projects/workspace.blade.php`
- Modify: `app/Http/Controllers/LandingPageController.php`
- Test: `tests/Feature/Projects/LandingPageTest.php`

**Interfaces:**
- Existing `POST /projects/{project}/landing-pages` remains the Shopify product/page endpoint.
- It accepts `title`, `page_url`, `selling_price`, `currency`, `specifications`, and one or more `sku_ids` belonging to the project.

- [ ] Change the landing-page feature test to submit a SKU created without a supplier source; assert a page-SKU join row, page activity, and redirect to the operations tab.
- [ ] Run `docker exec erp-redesign-demo php artisan test --filter=LandingPageTest` and confirm the old source requirement or UI copy fails the Shopify contract.
- [ ] Update form labels and record labels to “Shopify 产品/正式落地页”; show each linked market-research SKU, price and page URL in website operations.
- [ ] Keep the ownership and same-project validation in `LandingPageController`; include `shopify_product_linked` context in its activity payload.
- [ ] Re-run the focused test and commit `feat: link market sku to shopify pages`.

### Task 3: Require video and page attribution for Facebook tests

**Files:**
- Modify: `app/Http/Controllers/CampaignTestController.php`
- Modify: `resources/views/projects/workspace.blade.php`
- Test: `tests/Feature/Projects/CampaignTestTest.php`

**Interfaces:**
- Existing `POST /projects/{project}/campaign-tests` accepts required `creative_asset_id` and `landing_page_id` in addition to metrics.
- Both IDs must belong to the route project; `creative_asset_id` must identify a `video` asset.

- [ ] Write a failing test that creates a project video and Shopify page, posts a Facebook campaign using their IDs, and asserts both foreign keys are saved; add a request with another project’s asset and assert validation rejection.
- [ ] Run `docker exec erp-redesign-demo php artisan test --filter=CampaignTestTest` and confirm it fails because campaign input ignores selected content/page attribution.
- [ ] Validate IDs in `CampaignTestController`, set both fields while creating the campaign, and include video title and page title in the `campaign_test.created` activity payload.
- [ ] Add required “投放视频素材” and “Shopify 产品/落地页” selects to the traffic-growth campaign form; only list video assets and pages from the current project.
- [ ] Re-run the focused test and commit `feat: attribute facebook tests to video and shopify page`.

### Task 4: Fan out growth feedback to all affected departments

**Files:**
- Modify: `app/Http/Controllers/CampaignTestController.php`
- Modify: `resources/views/projects/workspace.blade.php`
- Test: `tests/Feature/Projects/CampaignTestTest.php`

**Interfaces:**
- Campaign request accepts `feedback_target_stages[]` containing unique values from `market_research`, `website_operations`, `content_creative`.
- When a note and targets are present, one campaign creates one `optimization_feedback` record per target inside the campaign transaction.

- [ ] Write a failing test that submits one Facebook result with all three feedback targets and asserts three feedback rows with the submitted note and a campaign ID.
- [ ] Run `docker exec erp-redesign-demo php artisan test --filter=CampaignTestTest` and confirm it fails because only one target can be created.
- [ ] Replace the single feedback target validation with an array validation; deduplicate targets, create all feedback rows transactionally, and record one activity per created feedback item.
- [ ] Replace the single target dropdown with three department checkboxes and clear Chinese outcome prompts in the traffic-growth form.
- [ ] Re-run focused campaign tests, then run `docker exec erp-redesign-demo php artisan test`; commit `feat: fan out growth feedback to affected teams`.
