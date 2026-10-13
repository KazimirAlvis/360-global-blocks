# Patient-Submitted Reviews block

Insert **Patient-Submitted Reviews** (`global360blocks/patient-submitted-reviews`) from the 360 Blocks category. Requires Platform Core's public patient-review repository (Core 1.1.0+; Core 1.2.0+ for Trash moderation). No automatic clinic-page integration or CF7 configuration is added.

Settings: optional heading; section background color; editable Patient Reviews button text (default “View All Reviews,” with an empty value hiding the button); clinic (All clinics by default); doctor (available only after choosing a clinic; All doctors includes clinic-wide reviews); review count 1–50 (default 9). Changing clinic clears the doctor filter. Invalid/stale doctor relationships fail closed. Published directory choices come from an editor-only REST endpoint requiring `edit_posts`; no private reviews are returned by that endpoint.

Server rendering calls `global360_platform()->patient_reviews()->query()` for every render. It uses that approved-only, newest-first public projection and its clinic/doctor permalinks. Private email, consent, moderation metadata, and pending/rejected/hidden/trashed records never become block attributes or public output. The editor uses WordPress ServerSideRender, including an explanatory empty state and refresh control. Public output is empty when no reviews match.

The manual Patient Reviews Slider still uses its saved reviews, editor controls, and original schema output. Both blocks share `inc/review-slider.php`, the Why Choose Us-inspired white card presentation, and the original block's built view script. Responsive regrouping shows three cards above 900px and one below; each instance manages its own controls with inactive-slide focus isolation, accessible slide status/current dots, and a defensive fallback for legacy markup without navigation. Submitted-review clinic and doctor links use the Primary Color from 360 Settings, and its dot controls provide 44px targets around 10px visible circles. The editor presents a static preview of returned submitted-review cards.

Caching: singular posts containing this block (including synced-block references) send no-cache headers, set DONOTCACHEPAGE, and invoke WP Fastest Cache's exclusion API. Rendering also invokes that exclusion for other contexts, including empty results. On the core moderation event, direct published block host pages are purged with WP Fastest Cache's post-cache hook. If the block is in a synced block or block template, the WP Fastest Cache full-page cache is purged because its usage can span many URLs. Any separate CDN/reverse-proxy must honor no-cache or receive its own invalidation integration. Dynamic rendering updates on the next request, not via live polling of already-open tabs.

Build from the plugin directory:

```sh
npm ci --no-audit --no-fund
npm run build:patient-reviews-slider
npm run build:patient-submitted-reviews
npm run build:styles-bundle
node tests/review-slider.cjs
php -d mysqli.default_socket=/Applications/MAMP/tmp/mysql/mysql.sock tests/patient-submitted-reviews.php
```

The integration suite creates and removes only synthetic local reviews/pages. `--preview` in an interactive terminal pauses for desktop/mobile checks; Enter cleans up. Use current Core when testing Trash. The JavaScript suite covers multiple instances, responsive grouping, wraparound controls, ARIA/current state, inactive focus, and the unchanged manual slider. Generated build files and the site-wide CSS bundle are part of the local change. No database content or reviews need migration.
