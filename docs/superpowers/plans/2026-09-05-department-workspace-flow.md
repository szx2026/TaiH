# Department Workspace Flow Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make each department workspace self-contained and model the requested research, website SKU collaboration, creative, and traffic-test workflow.

**Architecture:** Extend current Laravel records with nullable compatibility fields, then update controllers, workspace rendering, and targeted feature tests. Department URLs remain project lists, but project workspace return navigation preserves the originating department.

**Tech Stack:** Laravel 12, PHP 8.4, SQLite/MySQL migrations, Blade, PHPUnit.

**Spec:** `docs/superpowers/specs/2026-09-05-department-workspace-flow.md`

## Global Constraints

- Preserve existing product, SKU, asset, and campaign records.
- Use the four department codes exactly: `market_research`, `website_operations`, `content_creative`, `traffic_growth`.
- Use test-first changes and run feature tests inside the existing PHP Docker container.
- Do not collect website product page price, currency, specifications, or selling-point data.

---

### Task 1: Department-only navigation and workspace return path

**Files:**
- Modify: `app/resources/views/components/layouts/app.blade.php`
- Modify: `app/resources/views/projects/index.blade.php`
- Modify: `app/resources/views/projects/workspace.blade.php`
- Test: `app/tests/Feature/Layout/ApplicationShellTest.php`

**Interfaces:**
- Consumes: `stage` query parameter from department list routes.
- Produces: `return_stage` query parameter on workspace links.

- [ ] Write a failing shell test asserting Product Center is absent and all four department links remain.
- [ ] Run `docker exec erp-redesign-live php artisan test --filter=ApplicationShellTest` and verify the Product Center assertion fails.
- [ ] Remove the Product Center nav item; add `return_stage=$filters['stage']` to project workspace links; map workspace back link to `projects.index` with that stage.
- [ ] Re-run the focused test and verify it passes.
- [ ] Commit `feat: keep project work inside department workspaces`.

### Task 2: Research evidence presets, custom source, and repeatable intake

**Files:**
- Create: `app/database/migrations/2026_09_05_000019_add_custom_source_name_to_research_sources.php`
- Modify: `app/app/Http/Controllers/ResearchSourceController.php`
- Modify: `app/app/Models/ResearchSource.php`
- Modify: `app/resources/views/projects/workspace.blade.php`
- Test: `app/tests/Feature/Projects/ResearchSourceIntakeTest.php`

**Interfaces:**
- Consumes: `platform` in `tiktok|facebook_ads|amazon|independent_store|other` and optional `custom_source_name`.
- Produces: persistent evidence record with source label and evidence note.

- [ ] Write failing tests for `independent_store`, `other` plus `custom_source_name`, and two records on one project.
- [ ] Run `docker exec erp-redesign-live php artisan test --filter=ResearchSourceIntakeTest` and verify the new assertions fail.
- [ ] Add nullable `custom_source_name`; validate new choices and require custom name only for `other`; render the four preset labels and conditional custom-source field.
- [ ] Re-run focused test and verify it passes.
- [ ] Commit `feat: support repeatable research evidence sources`.

### Task 3: Reciprocal website SKU-requirement collaboration

**Files:**
- Modify: `app/app/Http/Controllers/ProjectDecisionController.php`
- Modify: `app/resources/views/projects/workspace.blade.php`
- Test: `app/tests/Feature/Projects/ProjectDecisionTest.php`

**Interfaces:**
- Consumes: `decision_type=sku`, `requested_from_stage` and request text.
- Produces: open decision shown to the target department on its relevant workspace tab.

- [ ] Write failing tests for website operations requesting SKU specifications from market research and market research asking website operations for SKU requirements.
- [ ] Run `docker exec erp-redesign-live php artisan test --filter=ProjectDecisionTest` and verify the display/redirect assertions fail.
- [ ] Render website “网站产品 SKU 规格需求” and research “询问详情页 SKU 制作要求” cards; redirect each request to the origin department workspace tab and display incoming open requests.
- [ ] Re-run focused test and verify it passes.
- [ ] Commit `feat: connect research and website sku requirements`.

### Task 4: Simplify Shopify page record and auto-title it

**Files:**
- Modify: `app/app/Http/Controllers/LandingPageController.php`
- Modify: `app/resources/views/projects/workspace.blade.php`
- Test: `app/tests/Feature/Projects/LandingPageTest.php`

**Interfaces:**
- Consumes: `page_url`, `sku_ids[]`.
- Produces: `LandingPage.title` set to `ProductProject.product_name`.

- [ ] Write a failing test posting a page without title, price, currency, or specifications and asserting title equals product name.
- [ ] Run `docker exec erp-redesign-live php artisan test --filter=LandingPageTest` and verify validation fails.
- [ ] Remove page-title and removed-field validation/UI; set title from project name and retain URL/SKU association.
- [ ] Re-run focused test and verify it passes.
- [ ] Commit `feat: simplify shopify page intake`.

### Task 5: Creative types, reference source, and upload guidance

**Files:**
- Create: `app/database/migrations/2026_09_05_000020_add_asset_types_to_creative_assets.php`
- Modify: `app/app/Http/Controllers/CreativeAssetController.php`
- Modify: `app/app/Models/CreativeAsset.php`
- Modify: `app/resources/views/projects/workspace.blade.php`
- Test: `app/tests/Feature/Projects/CreativeAssetTest.php`

**Interfaces:**
- Consumes: `asset_types[]` from `video|image|gif|copy`, `source_type` from `tiktok|youtube|other`.
- Produces: JSON `asset_types`; legacy `asset_type` receives first selected value for compatibility.

- [ ] Write failing tests storing video plus GIF and a YouTube reference.
- [ ] Run `docker exec erp-redesign-live php artisan test --filter=CreativeAssetTest` and verify validation fails.
- [ ] Add nullable JSON `asset_types`, validate type array and sources, save first type to legacy field, and replace select with multi-checkbox controls plus an upload placeholder.
- [ ] Re-run focused test and verify it passes.
- [ ] Commit `feat: support multi-type creative uploads`.

### Task 6: Traffic conversion metrics and ad-detail screenshot

**Files:**
- Create: `app/database/migrations/2026_09_05_000021_add_funnel_metrics_to_campaign_tests.php`
- Modify: `app/app/Http/Controllers/CampaignTestController.php`
- Modify: `app/app/Models/CampaignTest.php`
- Modify: `app/resources/views/projects/workspace.blade.php`
- Test: `app/tests/Feature/Projects/CampaignTestTest.php`

**Interfaces:**
- Consumes: `spend`, `single_clicks`, `add_to_cart_conversions`, `checkout_conversions`, optional `ad_detail_image`.
- Produces: persisted funnel metrics and optional local screenshot path.

- [ ] Write failing test for the four requested metrics and an uploaded PNG screenshot.
- [ ] Run `docker exec erp-redesign-live php artisan test --filter=CampaignTestTest` and verify the new request is rejected.
- [ ] Add nullable metric/image columns; validate and store screenshot; show metrics and screenshot link on saved test cards; retain old metrics for legacy cards.
- [ ] Re-run focused test and verify it passes.
- [ ] Commit `feat: record ad funnel metrics and screenshot`.

### Task 7: Full verification and demo data refresh

**Files:**
- Modify: `app/.demo.env` only if local runtime configuration requires it; never commit it.

- [ ] Run `docker exec erp-redesign-live php artisan test` and require all tests to pass.
- [ ] Seed/update local demo records for every department using the new evidence, requests, creative types, and campaign metrics.
- [ ] Verify all four department pages and the traffic project feedback page in the in-app browser.
- [ ] Push the committed branch to `origin/redesign-phase-one`.
