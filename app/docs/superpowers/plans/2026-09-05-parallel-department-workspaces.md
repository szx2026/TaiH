# Parallel Department Workspaces Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make department workbenches the only current product workflow and complete the missing parallel collaboration and campaign record features.

**Architecture:** Department workbench queries will expose the selected product and department-specific incoming work. The legacy project workspace becomes a redirect. Campaign tests add four requested measures and one stored image while preserving legacy database values for existing records.

**Tech Stack:** Laravel 12, Blade, Eloquent, SQLite/MySQL-compatible migrations, PHPUnit.

**Spec:** `docs/superpowers/specs/2026-09-05-parallel-department-workspaces-design.md`

## Global Constraints

- Only the four named departments are operational entry points.
- Forms must remain usable without API connections.
- Existing data must remain readable.

---

### Task 1: Replace the legacy workspace entry point

**Files:**
- Modify: `app/Http/Controllers/ProjectWorkspaceController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Projects/ProjectWorkspaceTest.php`

- [ ] Write a failing test asserting `/projects/{id}/workspace?return_stage=market_research` redirects to `/projects?stage=market_research&project={id}`.
- [ ] Run the test and confirm it fails because the old workspace returns HTML.
- [ ] Replace workspace rendering with a redirect to the requested valid department stage, falling back to the project's current stage.
- [ ] Run the test and confirm it passes.

### Task 2: Make project access parallel and role-aware

**Files:**
- Modify: `app/Http/Controllers/ProductProjectController.php`
- Modify: `resources/views/projects/index.blade.php`
- Test: `tests/Feature/Projects/ProductCenterTest.php`

- [ ] Write failing tests showing a product can be selected from every department workbench and that an unrelated member does not receive an editable form.
- [ ] Run the tests and confirm the current-stage query/form output fails the expectations.
- [ ] Query active products for department workbenches, add recipient decision data, and render only the active department's form plus relevant dependency summaries.
- [ ] Run the tests and confirm they pass.

### Task 3: Complete SKU request visibility and evidence-source interaction

**Files:**
- Modify: `resources/views/projects/index.blade.php`
- Modify: `app/Http/Controllers/ResearchSourceController.php`
- Test: `tests/Feature/Projects/ResearchSourceIntakeTest.php`
- Test: `tests/Feature/Projects/ProjectDecisionTest.php`

- [ ] Write failing tests for four evidence presets plus custom source text and for an open Website Operations SKU request appearing to Market Research.
- [ ] Run the tests and confirm they fail.
- [ ] Render the recipient requests in the relevant workbench and change research-source validation/UI to retain the four fixed presets with an optional custom source name.
- [ ] Run the tests and confirm they pass.

### Task 4: Replace campaign metrics and add image upload

**Files:**
- Create: `database/migrations/2026_09_05_000021_add_requested_metrics_to_campaign_tests_table.php`
- Modify: `app/Models/CampaignTest.php`
- Modify: `app/Http/Controllers/CampaignTestController.php`
- Modify: `resources/views/projects/index.blade.php`
- Test: `tests/Feature/Projects/CampaignTestTest.php`

- [ ] Write a failing test posting spend, CPC, add-to-cart conversions, checkout conversions and an image, then asserting stored values/path.
- [ ] Run the test and confirm validation fails because the fields do not exist.
- [ ] Add nullable columns, validate/store the requested fields and image, and replace legacy fields in the Traffic Growth form and record summary.
- [ ] Run the test and confirm it passes.

### Task 5: Remove stale duplicate forms and verify end to end

**Files:**
- Modify: `resources/views/projects/index.blade.php`
- Test: `tests/Feature/Projects/ProductCenterTest.php`

- [ ] Write a failing page assertion that no `asset_type` single-select, `original` source option, price input or specifications textarea exists in the department workbench.
- [ ] Run the test and confirm legacy markup causes it to fail.
- [ ] Delete the legacy markup instead of hiding it and render only the approved forms.
- [ ] Run the focused tests and full suite; inspect all four workbenches in the browser.
