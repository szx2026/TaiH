# Cross-border Product ERP Design

## Goal

Build an internal browser-based ERP for cross-border product research, website operations, creative production, and traffic growth. The system replaces the current spreadsheet workflow while keeping both manual entry and future automated data ingestion.

## Users and departments

- Market Research: find product opportunities, record TikTok/Facebook/marketplace evidence, and propose product and SKU directions.
- Website Operations: maintain 1688 sources, product details, internal SKU creation and return, plus landing-page versions, price, and specification presentation.
- Content Creative: create and version video, image, copy, and page assets based on product and landing-page needs.
- Traffic Growth: run Facebook and future channel tests, record results, and send optimization requests to the responsible department.
- Managers and system administrators: view all work, maintain memberships and settings, and audit changes.

## Product lifecycle

1. Market Research creates a product project from a source such as TikTok, Facebook Ad Library, Amazon, Shopify, Lightanda, Easy, or another marketplace.
2. Website Operations adds 1688 sources, product parameters, and internal SKU codes after creating them in the company SKU system.
3. Website Operations and Market Research can jointly propose and confirm SKU changes.
4. Website Operations creates landing-page requirements and versions; Content Creative creates assets using product, SKU, and landing-page context.
5. Traffic Growth tests a product/SKU/landing-page/asset combination and records performance.
6. Results can request landing-page, price, specification, SKU, or creative changes. The project is therefore a feedback loop, not a one-way pipeline.

## First-release scope

- Authentication, department memberships, roles, and audit log.
- Product-project list, detail view, lifecycle status, and ownership.
- Market-research sources, evidence, selection rationale, and SKU suggestions.
- 1688 source records, parameters, internal SKU lifecycle, and landing pages.
- Creative requests, private asset uploads, versions, and review status.
- Campaign tests, manual metrics entry, reviews, and optimization requests.
- Cross-department tasks, comments, attachments, submit/accept/return actions, and notifications.
- Private object storage integration with Cloudflare R2 using signed browser uploads.

## Deferred scope

- Automated marketplace crawling, automatic Facebook metrics syncing, AI scoring, inventory, ordering, logistics, finance, and advanced attribution.
- Every first-release record must still support manual creation, editing, file upload, and external-link entry.

## Technical constraints

- Primary deployment target: SiteGround Cloud on `erp.<company-domain>`, after confirming the account's PHP Manager provides PHP 8.4.1 or later.
- File storage: private Cloudflare R2 bucket. The relational database stores metadata only; browser clients upload/download with short-lived signed URLs.
- Database: MySQL.
- Every field written by a later import, crawler, API, or webhook must track source, sync time, sync result, and a manual-lock flag.
- All private files and business records require authenticated, role-checked access. Use HTTPS and retain auditable history for important changes.
