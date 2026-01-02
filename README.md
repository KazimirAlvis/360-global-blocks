# 360 Global Blocks

A comprehensive WordPress Gutenberg blocks plugin designed specifically for healthcare and medical practice websites. This plugin provides a collection of custom blocks that integrate seamlessly with medical practice management systems and patient engagement tools.

## Features

### 🏥 Popular Practices Block

Showcase clinic networks with logos, names, and clickable links. Integrates with custom clinic post types or can display random practices.

### 💊 Info Cards Block

Three-column medical information cards featuring:

-   12 curated medical icons from Health Icons library
-   Customizable titles and descriptions
-   Responsive grid layout

### 🎯 Hero Blocks

-   **Full Hero Block**: Full-page hero with background image and assessment CTA
-   **Simple Hero Block**: Clean page title display

### 📋 Assessment Integration

CTA and content blocks with built-in PatientReach360 assessment integration for patient engagement.

### 🖼️ Two Column Slider

Interactive content slider featuring:

-   Text content with images
-   Navigation arrows and dots
-   Autoplay functionality
-   Responsive design

### 👨‍⚕️ Find Doctor Block

Dedicated doctor search integration with customizable content and imagery.

### 📰 Latest Articles Block

Display recent blog posts and medical articles with:

-   Configurable post count
-   Excerpt length control
-   Featured image support
-   Responsive grid layout

### 📹 Video Two Column Block

Video content display with:

-   YouTube embed support
-   Assessment integration
-   Responsive design

### ✏️ Two Column Content Block

Streamlined editorial experience powered by native Gutenberg features:

-   Uses core InnerBlocks for the body area, so editors can add paragraphs, lists, quotes, and headings without custom tooling
-   Auto-migrates legacy body fields into the new InnerBlocks layout the next time a page is updated
-   Sanitizes leftover placeholder HTML (duplicate images, headings, CTA text) to keep both editor and front end clean
-   Server-side render callback ensures the InnerBlocks markup displays on the front end while preserving legacy fallbacks

### 🪄 Two Column Text Block

-   Purpose-built for copy-only layouts with two independent text columns
-   Each column is powered by core blocks (headings, paragraphs, lists, quotes, buttons, etc.)
-   Dedicated color picker/text field per column for quick branded background accents
-   Responsive layout that stacks gracefully on smaller breaks

### 🔗 Two Column CTA Block

-   1140px max-width split layout with an 800px content column and 300px CTA column
-   Rich text left column supports headings, paragraphs, lists, quotes, and links via InnerBlocks
-   Inspector controls for button label/URL along with a background color picker
-   Centered grid automatically collapses to a stacked layout on tablet and mobile

### ❓ FAQ Accordion Block

-   Drag-friendly list of questions with inline RichText editors for both prompts and answers
-   Palette-aware color pickers let you set the overall section background plus a highlight for the open dropdown
-   Accessible accordion markup keeps only one answer expanded at a time and mirrors the live animation inside Gutenberg

## Medical Focus

Built specifically for healthcare websites with features like:

-   **Health Icons Integration**: 1000+ medical icons (CC0 licensed)
-   **Clinic Post Type Support**: Automatic integration with clinic management
-   **Patient Assessment Tools**: PatientReach360 integration
-   **Medical-focused Styling**: Healthcare-appropriate designs
-   **Accessibility Compliance**: Built with healthcare accessibility in mind

## Installation

1. Upload the plugin files to `/wp-content/plugins/360-Global-Blocks/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to any page/post editor to find the blocks under "360 Blocks" category

## Requirements

-   WordPress 5.0+
-   PHP 7.4+
-   Node.js 14+ (for development)

## Development

### Setup

```bash
npm install
```

### Build all blocks

```bash
npm run build
```

### Build individual blocks

```bash
npm run build:info-cards
npm run build:popular-practices
npm run build:two-column-slider
# etc.
```

### Development mode (watch)

```bash
npm run dev
```

## Block Structure

Each block follows WordPress best practices:

-   `block.json` for metadata
-   React components for editor interface
-   PHP render callbacks for frontend
-   Separate SCSS files for styling
-   Individual build processes

## Tech Stack

-   **WordPress Gutenberg API**: Block development framework
-   **React/JSX**: Interactive editor components
-   **SCSS**: Modular styling system
-   **wp-scripts**: WordPress build tools
-   **Health Icons**: Medical icon library

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/new-block`)
3. Make your changes
4. Build the blocks (`npm run build`)
5. Commit your changes (`git commit -am 'Add new block'`)
6. Push to the branch (`git push origin feature/new-block`)
7. Create a Pull Request

## License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## Support

For support and feature requests, please create an issue on GitHub.

## Changelog

### 1.3.58

-   The Two Column block now automatically pulls WordPress media alt text inside the editor and ditches the redundant manual field, so preview copy always mirrors the media library entry.
-   Server rendering now prefers the attachment alt text (and falls back to the attachment title or block heading), guaranteeing descriptive `alt` copy even if legacy attributes are empty.

### 1.3.57

-   Added an explicit alt-text control to the Two Column block that auto-seeds from the media library and renders on the frontend, keeping layout imagery descriptive instead of shipping empty `alt` attributes.
-   Ensured Video Two Column lite YouTube thumbnails reuse the video title (or block heading) for their `alt` text so autoplay placeholders meet accessibility expectations.

### 1.3.56

-   Added a Yoast SEO editor bridge that surfaces the first Global Blocks image to Gutenberg so Yoast's analysis no longer claims "No images appear on this page" when content relies on block-sourced media.
-   Localizes the collector output through the block editor enqueue, dispatches `setContentImage()` in the `yoast-seo/editor` store, and only loads the helper when Yoast and a supported post type are present.

### 1.3.55

-   Added the Patient Reviews Slider block with palette-aware background colors, brand-accented dots/arrows, rounded review cards, and synchronized dot/arrow navigation.
-   Added the One Column Video block (YouTube URL + heading) with a centered 1140px inner width, 46px max heading size, and editable background color.

### 1.3.54

-   Removed extra padding on `.main-paragraph-con` inside the Video Two Column body.

### 1.3.53

-   Added a “None” option to the Info Cards icon selector so cards can render without an icon.

### 1.3.52

-   Extended the Comparison Table background color picker to blanket the full-width section while keeping the table, heading, and footnote centered inside a 1140px inner wrapper for consistent layout alignment.
-   Synced the table header/cell borders so every column shows subtle dividers and the header row typography stays sentence-case instead of forcing uppercase row labels.
-   Removed the outer drop shadow from the FAQ Accordion wrapper so it can sit flush against tinted landing page backgrounds while retaining per-item elevation cues.

### 1.3.51

-   Added a Comparison Table block with adjustable column and row counts so marketers can recreate the treatment matrices shown on service-line pages without relying on Core tables.
-   Synced the highlighted header row with the primary color stored in 360 settings (falling back to `--cpt360-primary`) so the hero row always matches the site palette.
-   Shipped responsive frontend/editor styles plus manifest wiring so the new block renders semantic tables with accessible captions and zebra striping on every template.

### 1.3.50

-   Always enqueues `global-shared.min.css` so blog templates that omit CTA-style blocks still load the centered Page Title Hero heading rules.
-   Strengthened the shared `.sm_hero h1` declaration with `!important` values so theme typography resets can’t strip the 1140px width or 1.4 line-height.

### 1.3.49

-   Forces the Page Title Hero `<h1>` to keep the centered 1140px max width even when a theme injects its own `.sm_hero h1` declarations.
-   Rebuilt the block so both the editor preview and frontend CSS keep the 1.4 line-height and `margin: 0 auto` across every breakpoint.

### 1.3.48

-   Locked the Page Title Hero heading to a centered 1140px max width with `margin: 0 auto` so long health system names no longer spill past the grid.
-   Dialed the H1 line-height to 1.4 and rebuilt the block assets so both the editor preview and frontend hero share the same tightened typography.

### 1.3.47

-   Upgraded the Video Two Column body field to use native Gutenberg blocks, so editors can drop in bullet or numbered lists without leaving the block.
-   Automatically migrates existing body copy into the new InnerBlocks area while keeping the PHP render and frontend styles in sync for paragraphs and lists.

### 1.3.46

-   Added a palette-aware background color picker to the Video Two Column block so the entire section can align with branded surfaces without custom CSS.
-   Updated the editor, frontend CSS, and PHP render wrapper so the selected color fills the full-width background rather than just the content column.

### 1.3.45

-   Added the same media position toggle from the standard Two Column block to the Video Two Column layout so editors can flip the video to either side without rebuilding content.
-   Updated the editor, frontend CSS, and PHP render class to honor the toggle, mirroring padding and grid order so the new layout looks identical in Gutenberg and on the site.

### 1.3.44

-   Kept the FAQ Accordion background full bleed while constraining the dropdown list to a centered 1140px max width with 20px gutters.
-   Rebuilt the FAQ bundle so the updated layout ships with GitHub updater installs.

### 1.3.43

-   Smoothed out the FAQ Accordion editor so answer fields are always visible, properly labeled, and editable in WordPress 6.9 without deprecated multiline warnings.
-   Relaxed the question and answer spacing on the frontend to keep multi-line prompts readable once dropdowns expand.

### 1.3.42

-   Introduced a dedicated FAQ Accordion block with reorderable question/answer pairs, inline RichText editing, and palette-aware controls for the section background plus the active dropdown state.
-   Added a lightweight frontend script that animates the accordion, keeps only one item open at a time, and syncs the editor preview with the live behavior.

### 1.3.41

-   Added palette-aware controls to the Two Column block so authors can pick standard background and heading colors without custom CSS.
-   Synced server rendering so heading text and content panel colors on the frontend match the editor preview.

### 1.3.40

-   Matched the Two Column CTA button styles (shape, hover, and default label) to the PatientReach360 assessment button.
-   Removed the extra padding on the CTA heading and paragraph wrappers so copy aligns with the rest of the section.

### 1.3.39

-   Introduced the Two Column CTA block featuring a rich text left column, configurable button on the right, and adjustable background color inside a 1140px grid.

### 1.3.38

-   Standardized repository and folder naming to lowercase (`360-global-blocks`) to ensure consistent plugin paths across development and production environments.
-   Updated manifest URL to point to the renamed GitHub repository.

### 1.3.37

-   Fixed a missing closing `</div>` tag in the Two Column Slider render function that was causing all subsequent page content to be incorrectly wrapped inside the slider container.

### 1.3.36

-   Fixed the responsive flow for the Two Column layout toggle so right-aligned images stack above the copy on tablet/mobile viewports instead of fighting the desktop grid order.
-   Simplified the markup to always render the media column first and let CSS handle the swap, preventing theme overrides from breaking the stacked layout.

### 1.3.35

-   Added an inspector layout toggle to the Two Column block so editors can swap the media column to either side without duplicate blocks.
-   Updated both editor and frontend CSS to honor the new layout choice, including mirrored padding (40px 80px 40px 30px) when the image sits on the right.

### 1.3.34

-   Removed the grid gap inside the Two Column block so the image column can stretch flush to the viewport edge on wide screens.
-   Locked the desktop padding to `40px 30px 40px 80px` to align the text column with the Video Two Column block and rebuilt the block assets.

### 1.3.33

-   Eliminated residual padding on legacy `main-*-con` wrappers inside the two-column body so text now aligns cleanly with its heading on both the front end and in the editor preview.
-   Recompiled the two-column block assets to ensure every install picks up the refined spacing defaults.

### 1.3.32

-   Bundled the Health Icons catalog into the Info Cards editor build so the full picker loads without separate script enqueues.
-   Converted the CTA heading to a RichText field, letting authors update the hero copy inline while the frontend stays sanitized.
-   Rebuilt affected block assets and refreshed release metadata for the GitHub updater.

### 1.3.31

-   Split every block into dedicated `edit.js` and streamlined `view.js` bundles so frontend pages stay free of editor-only dependencies.
-   Rewired the manifest-driven loader so PHP enqueues the new view builds only when matching blocks render.
-   Removed the complex health icon loader and refreshed shared assets to reduce plugin overhead.

### 1.3.30

-   Replaced the Full Hero CSS background with an eager `<img>` element so browsers can surface the hero art as the page’s Largest Contentful Paint, complete with width/height metadata and alt text.
-   Dialed in responsive typography for the Full Hero heading and subheading, keeping desktop scale bold while ensuring tablet and mobile breaks remain legible.
-   Regenerated the Full Hero build artifacts and block manifest to keep WordPress in sync after the markup and style changes.

### 1.3.29

-   Introduced a shared `global-shared.min.css` bundle that auto-enqueues whenever a PatientReach360 questionnaire button renders, keeping About and interior pages styled even without the CTA block present.
-   Rebuilt frontend assets so block-specific styles no longer duplicate the questionnaire button rules, trimming CSS size across CTA, Full Hero, Two Column, and Video Two Column blocks.
-   Removed the unused legacy `global360blocks/hero` scaffold to avoid “unsupported block” warnings in Gutenberg.

### 1.3.28

-   Converted the Video Two Column block to a lite YouTube embed so the heavy player only loads after visitors click play, cutting unused network requests on initial page load.
-   Added a `global360blocks_video_two_column_use_lite_embed` filter so theme developers can fall back to the classic iframe renderer without touching plugin code.

### 1.3.27

-   Added a manifest-driven asset loader so frontend CSS/JS for each block only loads when that block renders, trimming unused styles on pages without 360 content.
-   Ensured the Latest Articles bundle always loads on blog/archive templates and preloaded hero styles whenever those blocks sit above the fold to prevent flashes of unstyled content.
-   Exposed filters so theme developers can extend the forced-asset list without patching the plugin directly.

### 1.3.26

-   Hardened the Video Two Column block layout with inline flex fallbacks so the video URL input no longer overlaps the new title field when cached admin CSS lingers.
-   Rebuilt the block bundle and recopied manifests so the layout safeguard ships with the update.

### 1.3.22

-   Unified typography across every block so headings and buttons now respect the theme-provided `--heading-font`, `--body-font`, and matching weight variables (defaulting to 400).
-   Regenerated all block assets after the typography sweep to keep compiled CSS in sync with the updated font rules.
-   Ensured build scripts recopied block manifests/render files so WordPress continues to register each block after updates.

### 1.3.21

-   Flattened the Two Column Slider layout on phones with tighter padding, softer shadows, and responsive typography so cards feel more like lightweight callouts
-   Automatically hides the slide image below tablet breakpoints to prioritize copy and shrink the overall mobile height without editor tweaks
-   Mirrored the responsive rules inside the block editor so authors see the same single-column experience while adjusting content

### 1.3.20

-   Prevent duplicate fallback clinics in the Popular Practices block by sampling unique posts and only rendering the slots you have content for
-   Auto-center two or three cards with responsive grid sizing and enlarge clinic logo pads to 150px for better brand presentation
-   Bundled slider/popular practices metadata in the build output to ensure WordPress recognizes both blocks after updates

### 1.3.19

-   Added a per-slide background color picker to the Two Column Slider so each card can carry unique brand accents without custom CSS
-   Clamped slider imagery to a 700px max height (with mobile reset) to keep oversized assets from stretching the layout
-   Ensured the build pipeline ships the slider `block.json` metadata so existing pages keep rendering after updates

### 1.3.18

-   Smoothed the Two Column Text editor experience so columns sit side by side in Gutenberg while still stacking responsibly on smaller breakpoints
-   Trimmed grid styling from the wrapper classes that WordPress injects so front-end layouts stay intact without affecting the admin canvas
-   Resolved the editor preview error by bundling the block-editor data hooks directly with the block script after the latest enhancements

### 1.3.17

-   Introduced the **Two Column Text** block featuring two rich-text columns and per-column background color controls backed by native InnerBlocks
-   Registered build tooling and assets for the new block so it ships with npm builds and GitHub updates
-   Ensured plugin registration loads the static block automatically without additional render callbacks
-   Refined the editor locking so column structure stays fixed while paragraph → list transforms and other core conversions remain available

### 1.3.16

-   Fixed the two-column block to persist InnerBlocks markup on save so migrated body content renders on the front end without manual HTML tweaks
-   Updated the GitHub updater safeguards to retain the mixed-case `360-Global-Blocks` directory name after installs and updates

### 1.3.15

-   Hardened the GitHub updater so branch archives are always renamed to `360-global-blocks`, preventing block registration warnings after updates
-   Added a safe recursive cleanup helper that removes stray `-main` folders before moving files into place

### 1.3.14

-   Adopted core InnerBlocks for the two-column block body, enabling rich text, lists, and other native blocks without custom fields
-   Added migration and sanitization logic that moves legacy `bodyText` content into InnerBlocks while stripping duplicate placeholders
-   Tightened server and client rendering so the editor preview matches the front end, including refreshed padding and typography

### 1.3.4

-   Synced production FTP adjustments back into the repo
-   Bumped version to trigger GitHub-powered auto-update

### 1.0.0

-   Initial release
-   8 custom blocks for healthcare websites
-   Health Icons integration
-   PatientReach360 assessment integration
-   Responsive designs for all blocks
