# Department Workspace Flow Design

## Goal

Keep each department in its own continuous work context while supporting the real selection-to-advertising workflow: product research, website SKU requirements, creative production, traffic testing, and cross-department adjustments.

## Navigation

The sidebar exposes only the four department workspaces, dashboard, feedback center, and administration. Product Center is removed. Department list rows open the project workspace with a `return_stage` value so the workspace back link returns to that same department rather than a generic product list.

## Market Research

Market research can add any number of evidence records. A record has a preset source (`tiktok`, `facebook_ads`, `amazon`, or `independent_store`) and an optional custom source name. The research workspace shows open SKU requests from website operations, then lets the researcher decide which internal SKUs to open and paste the generated SKU code back into the ERP.

## Website Operations

Website operations records the 1688 supplier source and uses a product page URL. The product page title is automatically the project product name; page price, page specifications, product selling points, and currency are not collected. The core operation is a website product SKU requirement request: it records the requested variants/sets and is delivered to market research. Website operations can also respond to research questions about which SKU format is needed.

## Content Creative

A creative asset supports one or more types from video, image, GIF, and copy. Reference type is `tiktok`, `youtube`, or `other`. File upload has clear copy that accepts a local source file, while external URL and script are still valid manual-entry alternatives.

## Traffic Growth

A campaign test records spend (USD), outbound clicks (`single_clicks`), add-to-cart conversions, and checkout conversions. It links the chosen video and Shopify page, can store one uploaded ad-detail screenshot, and creates feedback for one or more of the other three departments. Existing legacy impressions/clicks/conversions remain readable for previously entered records but new entries use the new metrics.

## Data and Compatibility

New nullable database columns preserve existing records: `research_sources.custom_source_name`; `creative_assets.asset_types` JSON; `campaign_tests.single_clicks`, `add_to_cart_conversions`, `checkout_conversions`, `ad_detail_image_disk`, and `ad_detail_image_path`. Existing single asset type, research platform values, and campaign metrics remain available so prior demo data and production imports are not lost.

## Validation and Authorization

Only market research can create research evidence or respond by opening internal SKUs. Only website operations can create SKU requirements and website sources/pages. Only content creative can create assets. Only traffic growth can create campaign tests. Uploaded files are optional only when the corresponding manual URL/text field is present; a campaign screenshot itself is optional.

## Tests

Feature tests cover department navigation without Product Center, multiple research evidence/custom source validation, reciprocal SKU requests, automatic page title, creative multi-type and GIF validation, and traffic metric/screenshot persistence. Existing workflow tests continue to protect permission boundaries and stage handoffs.
