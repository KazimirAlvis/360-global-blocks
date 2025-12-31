=== 360 Global Blocks ===
Contributors: kazalvis
Tags: gutenberg, blocks, healthcare, patientreach360
Requires at least: 6.0
Tested up to: 6.9
Stable tag: 1.3.57
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Custom Gutenberg blocks tailored for the 360 network. Includes hero layouts, CTA components, clinic listings, info cards, and PatientReach360 assessment integrations designed for healthcare organizations.

== Installation ==
1. Upload the plugin files to `/wp-content/plugins/360-global-blocks/` or install via the Plugins screen.
2. Activate through the Plugins screen.
3. Find the blocks under the "360 Blocks" category in the block editor.

== Release Workflow ==
1. Update `360-global-blocks.php`, `readme.txt`, and `plugin-manifest.json` with the new version, changelog, and compatibility data.
2. Commit and push to `main`—the manifest points to the `main` branch zip, so no manual release or asset upload is required.
3. In any WordPress install, visit Tools → 360 Blocks Updates and click "Force Update Check Now" to confirm the site sees the new version.
4. From the Plugins screen (or Dashboard → Updates) click "Update now" to install the branch build.

== Updater Notes ==
* Since version 1.3.7 the plugin auto-updater delivers the `main` branch archive from GitHub (`plugin-manifest.json` → `download_url`).
* The function `sb_global_blocks_rename_github_package()` (in `360-global-blocks.php`) renames GitHub's extracted folder to `360-global-blocks/` via the `upgrader_source_selection` filter so WordPress recognizes the plugin after extraction.
* Version 1.3.9 hardens that rename logic to ensure branch archives never leave a `-main` suffix in the plugin directory.
* Diagnostics remain available under Tools → 360 Blocks Updates for checking detected versions, manifest URL, and errors.

== Frequently Asked Questions ==

= Does this plugin require a GitHub access token? =
No. The updater fetches a JSON manifest over HTTPS. Keep the repository public or host the manifest on an accessible URL. If you make it private, proxy the manifest and ZIP download through an authenticated endpoint.

== Changelog ==

= 1.3.57 =
* Added an editor-facing image alt text field to the Two Column block that auto-fills from the media library and renders on the frontend so layout imagery is always descriptive.
* Video Two Column’s lite YouTube thumbnail now inherits the video title (or block heading) for its `alt` attribute, keeping the autoplay placeholder accessible when the iframe is deferred.

= 1.3.56 =
* Added a Yoast SEO editor bridge that surfaces the first Global Blocks image to Gutenberg so the SEO analysis no longer reports "No images appear on this page" when content relies on block imagery instead of core images.
* Localizes the collector output through the block editor enqueue, dispatches `setContentImage()` in the `yoast-seo/editor` data store, and only loads the helper when Yoast plus a supported post type is in play.

= 1.3.55 =
* Added the Patient Reviews Slider block with palette-aware background color, brand-accented dots/arrows, rounded review cards that stay white over tinted sections, and working arrow/dot navigation.
* Added the One Column Video block (YouTube URL + heading) with a centered 1140px inner width, 46px max heading size, and background color selection.

= 1.3.54 =
* Removed extra padding on `.main-paragraph-con` inside the Video Two Column body.

= 1.3.53 =
* Added a “None” option to the Info Cards icon selector so cards can render without an icon.

= 1.3.52 =
* Stretches the Comparison Table background color across the full-width section while constraining the heading, table, and footnote to a centered 1140px inner wrapper so layouts stay aligned with the rest of the site.
* Adds consistent vertical dividers across every Comparison Table column, keeps thead typography sentence-case, and removes the special row-heading treatment so each cell renders uniformly.
* Removes the outer box shadow from the FAQ Accordion container so it blends cleanly into tinted landing page sections while individual accordion cards retain their own elevation cues.

= 1.3.51 =
* Added a Comparison Table block with adjustable columns and rows so editors can rebuild treatment matrices without hacking the Core table block.
* Automatically paints the highlighted header row with the primary color stored in the 360 settings (falling back to `--cpt360-primary`) while shipping responsive zebra-striping styles for both the editor and frontend.

= 1.3.50 =
* Always enqueues the shared `global-shared.min.css` bundle so blog templates without CTA blocks still receive the Page Title Hero heading constraints.
* Hardened the shared `.sm_hero h1` rule with `!important` declarations so theme-level overrides can no longer strip the centered 1140px width or 1.4 line-height.

= 1.3.49 =
* Forces the Page Title Hero `<h1>` to inherit the centered 1140px max width even when a theme injects its own `.sm_hero h1` declarations.
* Rebuilt the block so the editor preview and compiled CSS keep the 1.4 line-height and `margin: 0 auto` at every breakpoint.

= 1.3.48 =
* Centered the Page Title Hero heading inside a 1140px max width with `margin: 0 auto` so long service-line names stay aligned with the rest of the hero grid.
* Tightened the H1 line-height to 1.4 and rebuilt the block assets so the editor and frontend share the same typography.

= 1.3.47 =
* Swapped the Video Two Column body field to native Gutenberg blocks so editors can add bullets, numbered lists, quotes, and multi-paragraph copy inside the block.
* Auto-migrates existing body text into the new InnerBlocks area and keeps the frontend render plus styles updated for the richer markup.

= 1.3.46 =
* Added a palette-aware background color picker to the Video Two Column block so marketing teams can tint the full-width section without writing CSS.
* Synced the editor UI, frontend styles, and PHP renderer so the selected color blankets the entire block instead of only the content column.

= 1.3.45 =
* Added the familiar Media position toggle from the standard Two Column block to the Video Two Column layout so the video can sit on either side of the content without rebuilding the block.
* Synced the editor/preview CSS and PHP render class with the new toggle so grid order and padding swap cleanly on both the front end and inside Gutenberg.

= 1.3.44 =
* Kept the FAQ Accordion background full-width while constraining the list of dropdowns to a centered 1140px max width with 20px side padding.
* Rebuilt the FAQ assets so the auto-updater delivers the refreshed layout without extra steps.

= 1.3.43 =
* Kept the FAQ Accordion answer editors always visible with clear labels and modern RichText props so editing works cleanly in WordPress 6.9.
* Increased line-height and padding on both the question prompt and expanded answer body to prevent multi-line copy from feeling cramped.

= 1.3.42 =
* Added a FAQ Accordion block with RichText-powered headings plus reorderable question/answer pairs so marketing teams can build SEO-friendly Q&A sections without custom code.
* Included palette-based background and active-row color pickers along with a frontend script that animates the dropdowns and keeps only one item open at a time.

= 1.3.41 =
* Added palette-driven background and heading color controls to the Two Column block, ensuring selections pull from the site’s standard swatches.
* Synced the frontend render so both the heading text and content panel background mirror the editor preview.

= 1.3.40 =
* Matched the Two Column CTA button styling and hover states to the PatientReach360 questionnaire button while defaulting the label to "Take Risk Assessment Now."
* Removed stray left padding on the heading/body wrappers so CTA copy aligns cleanly with the rest of the layout.

= 1.3.39 =
* Added the Two Column CTA block with an 800/300 split grid, InnerBlocks-powered content column, configurable button, and adjustable background color.

= 1.3.38 =
* Standardized repository and folder naming to lowercase (360-global-blocks) for consistent plugin paths across all environments.
* Updated manifest URL to match the renamed GitHub repository.

= 1.3.37 =
* Fixed a missing closing div tag in the Two Column Slider that caused all subsequent content blocks to be wrapped inside the slider container.

= 1.3.36 =
* Ensured the Two Column block stacks correctly on tablet and mobile when the image is set to the right column by letting CSS control the swap and resetting the responsive grid.
* Always renders the media column first in markup so themes can't override the mobile flow when layout toggles are enabled.

= 1.3.35 =
* Added a layout selector to the Two Column block so authors can flip the image to either the left or right column without rebuilding content.
* Synced the editor and frontend padding so right-aligned layouts get 40px 80px 40px 30px spacing, matching the rest of the hero lineup.

= 1.3.34 =
* Removed the leftover grid gap in the Two Column block so the media column can sit flush with the viewport edge on desktop.
* Set the desktop padding to 40px 30px 40px 80px and rebuilt the block so its text column now lines up with the Video Two Column layout by default.

= 1.3.33 =
* Removed the lingering padding applied by legacy `main-*-con` wrappers so two-column body copy now aligns flush with its heading in both editor and front-end renders.
* Rebuilt the two-column block bundle so the revised spacing defaults ship automatically with this update.

= 1.3.32 =
* Bundled the Health Icons catalog directly into the Info Cards editor build so the icon picker stays populated without extra scripts.
* Swapped the CTA heading to a RichText field so authors can edit the call-to-action copy inline while the frontend output remains sanitized.
* Rebuilt block assets and refreshed release metadata for the GitHub auto-updater.

= 1.3.31 =
* Split every block into dedicated `edit.js` and lightweight `view.js` bundles so editor dependencies no longer load on the frontend.
* Regenerated block manifests and PHP registration to enqueue the new view builds only when a block renders.
* Retired the complex health icon loader and refreshed shared assets to keep the plugin footprint lean.

= 1.3.30 =
* Swapped the Full Hero background layer to an eager `<img>` element so Largest Contentful Paint captures the hero art earlier while preserving alt text and intrinsic sizing.
* Tightened Full Hero typography across desktop and mobile with breakpoint-specific sizing to keep headings legible without overpowering smaller screens.
* Rebuilt the Full Hero bundle and recopied block metadata so WordPress registers the latest assets without manual intervention.

= 1.3.29 =
* Added a shared `global-shared.min.css` bundle that automatically loads whenever a PatientReach360 questionnaire button renders, keeping assessments styled on pages that skip the CTA block.
* Recompiled block assets after moving the questionnaire button rules out of individual blocks, reducing duplicate CSS across CTA, Full Hero, Two Column, and Video Two Column bundles.
* Removed the unused legacy `global360blocks/hero` scaffold to prevent “unsupported block” notices inside the block editor.

= 1.3.28 =
* Converted the Video Two Column block to a lite YouTube embed so the heavy player only loads after interaction, reducing unused requests on first paint.
* Added a `global360blocks_video_two_column_use_lite_embed` filter to opt back into the classic iframe renderer when needed without editing plugin code.

= 1.3.27 =
* Added a manifest-driven asset loader so block CSS/JS only loads on pages where the block renders, cutting down unused styles across the site.
* Always queue the Latest Articles stylesheet on blog/archive templates and preload hero bundles when detected above the fold to avoid flashes of unstyled content.
* Introduced filters so custom templates can extend the forced asset list without editing core plugin files.

= 1.3.12 =
* Aligned patient questionnaire buttons with the "Take Risk Assessment Now" copy across CTA, video two-column, two-column, and Find Doctor blocks, including hover styling fixes.
* Trimmed top margins on info cards and popular practices headings for tighter hero-to-content spacing.

= 1.3.11 =
* Widened the full hero, info cards, and video two-column block containers to match the latest site layout direction.
* Smoothed out padding and alignment, including a fully responsive video wrapper for the hero’s embedded media.

= 1.3.10 =
* Routine validation bump to confirm the branch-archive updater keeps working.

= 1.3.9 =
* Hardened the branch-archive updater so the plugin folder always remains `360-global-blocks` after updates.

= 1.3.8 =
* Confirmed the branch-archive updater path works end-to-end and documented the helper filter for future reference.

= 1.3.7 =
* Pulled the most recent FTP edits back into Git and lined up the 1.3.7 release package.

= 1.3.6 =
* Captured the FTP hotfixes into source control and prepped the manifest for the next GitHub release package.

= 1.3.5 =
* Replaced the Plugin Update Checker dependency with a lightweight manifest-driven updater.
* Added a GitHub-hosted `plugin-manifest.json` and refined the update diagnostics page.

= 1.3.4 =
* Switched to the Plugin Update Checker library for reliable GitHub updates.
* Synced the Git repository with the live FTP copy and updated diagnostics tooling.

= 1.3.3 =
* Version bump for live updater smoke test.
* Confirmed admin diagnostics tooling.

= 1.3.2 =
* Added update diagnostics page and slug fixes.

= 1.3.1 =
* Initial GitHub-based auto-update rollout.

= 1.0.0 =
* Initial release with hero, CTA, info cards, clinic listings, and PatientReach360 integration.
