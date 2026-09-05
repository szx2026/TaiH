# Parallel Department Workspaces Design

## Goal

Make the department workbench the sole operational entry point for a product, so the four departments collaborate on the same product in parallel without reviving the previous serial tabbed workspace.

## Product rules

- Market Research, Website Operations, Content Creative and Traffic Growth each open a product from their own workbench.
- A product is visible to every department when it has work relevant to that department; visibility is not restricted to `current_stage`.
- Each workbench presents only the department's editable work and a compact read-only summary of dependencies from the other departments.
- The former `/projects/{project}/workspace` route redirects to the relevant department workbench and may not render old forms.
- Website Operations enters website SKU requirements and Market Research receives an actionable list before creating internal SKUs.
- Campaign recording uses spend, CPC, add-to-cart conversions, checkout conversions and an optional advertising-detail image.

## Data design

`campaign_tests` gains nullable `cost_per_click`, `add_to_cart_conversions`, `checkout_conversions` and `detail_image_path`. Legacy impressions/clicks/conversions remain readable for existing records but are no longer requested by the new UI.

Project decisions remain the source of truth for SKU collaboration. A decision's `requested_from_stage` is the recipient department. Workbenches show open decisions addressed to the active department.

## UI design

The `/projects` page keeps one selected product card. It shows a department-specific main work card, a relevant incoming-work card, and a compact dependency summary. Forms are displayed only to the matching department (or administrators). No CSS-hidden legacy form is retained.

Research evidence offers exactly four presets: TikTok, Facebook Ads Library, Amazon and Independent Store. A separate optional custom source-name field records non-preset sources.

## Authorization and verification

Controllers remain the enforcement layer. The views additionally hide action forms from unrelated departments. Feature tests cover parallel project visibility, recipient decision visibility, campaign metric persistence/image upload, and legacy-workspace redirect behavior. The complete test suite and a browser check verify the result.
