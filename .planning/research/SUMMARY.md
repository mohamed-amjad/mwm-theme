# Project Research Summary

**Project:** MWM — Environmental Services Website (`mwmpower-ksa`), custom theme `mwm`
**Domain:** Arabic RTL-first B2B lead-generation corporate site for a Saudi NCEC-licensed environmental services establishment. Classic custom WordPress theme, edited through Elementor Free custom widgets, with ACF Free and CPT structured data.
**Researched:** 2026-10-04
**Confidence:** HIGH overall. Elementor internals flagged **[VERIFY]** below are MEDIUM until a spike proves them against the installed version.

## Executive Summary

MWM needs a small, fast, credibility-first B2B site. A Saudi facility owner (crusher, block or ready-mix plant, factory, mining site, roads authority, commercial building) should understand within seconds that MWM is NCEC-licensed for remediation and pollution-control filters, also designs ventilation systems, and can be reached in one tap by WhatsApp, phone or quote form. Experts build this kind of site, on a Free-only plugin stack, the way Al-Masa was built: a classic PHP theme owns the shell, data, forms, SEO and performance; Elementor Free provides visual editing through one custom widget per section; repeatable business records live in CPTs with ACF Free fields; and company-wide singletons live on one Settings API page. Al-Masa proved this pattern works on Elementor 4.3 with ACF 6.8. **Reuse the architecture, but rewrite every module.** Reading the Al-Masa code found 15 concrete defects: Egyptian phone logic, lookups by Arabic page title, Google Fonts from the CDN, a reveal animation hiding the hero, autoplay with no pause control, JS listener leaks in the editor, a 51 KB monolithic CSS file, and seeds that are not idempotent.

The recommended stack is current and verified: WordPress 7.1.1, PHP 8.3 locally with 8.2+ compatible code, Elementor 4.3.3 using the classic `Widget_Base` API (Atomic Editor disabled), and ACF 6.8.10 with PHP-registered field groups. Fonts are self-hosted variable Alexandria (headings) and Readex Pro (body), about 113 KB in total, as the free stand-in for 29LT Bukra. Frontend code is vanilla CSS/JS with no bundler and no Swiper, GSAP or GLightbox; sliders use scroll-snap, the lightbox uses `<dialog>`, and the WhatsApp number picker uses the Popover API. The theme emits its own JSON-LD (Organization, LocalBusiness per branch, Service, BreadcrumbList) and Open Graph tags, uses core sitemaps, and handles leads with a native form that writes to a private inbox CPT. The "better than Al-Masa" priorities each map to a specific architectural change: declarative content specs and style-part traits give every widget full controls; a token bridge makes Elementor Kit globals the single color and font source; a schema-driven settings page with a page-role registry replaces title lookups; a versioned, idempotent seeder builds the site reproducibly; and per-widget CSS is enqueued in `<head>`.

The main risks fall into five groups:
- **Content integrity.** Licence overclaim on ventilation, invented stats or addresses, mislabeling the entity as «شركة» vs «مؤسسة», the "Sonolite" typo, and photo ownership and resolution. The PDF photos top out at 1280 px, so full-bleed 1920 heroes would look soft.
- **Elementor persisted-schema traps.** Widget names and control IDs are a data schema. Dynamic values placed in control defaults get frozen into saved pages.
- **WCAG contrast.** `#5BBF75` and `#A5D7FC` fail on white, even for non-text UI.
- **Hero LCP and font payload**, both needed for Lighthouse 90+.
- **Silently lost leads.** Gmail plus PHP `mail()`, cached nonces, and throttles keyed on a proxy IP.

Each has a concrete prevention mapped to a phase below. The inbox CPT is the system of record, so email failure never loses a lead.

## Key Findings

### Recommended Stack

Details are in [STACK.md](./STACK.md). Versions were verified against official registries and on this machine. Nothing ships to visitors except the theme's own CSS/JS and two font families on top of Elementor's own frontend runtime (jQuery included). That runtime is the performance floor, so the theme's share must stay small. Dev tooling lives in `tools/`, outside the theme: WP-CLI phar, Composer phar, PHPCS 3.13 + WPCS 3.4.1, mupdf 1.28.1, Playwright 1.63, Lighthouse 13.5 and Mailpit.

**Core technologies:**
- **WordPress 7.1.x** (installed, locale `ar`, `/%postname%/`): CMS, media, CPTs, core sitemaps and WebP sub-sizes. It is the only maintained branch. Do **not** re-run `wp core install`; the client already installed WordPress.
- **PHP 8.3 (dev) / 8.3–8.4 (prod); code floor 8.2**: the theme header says `Requires PHP: 8.2`. Confirm the host's version in Phase 1.
- **Custom classic theme `mwm`**: Elementor Free has no Theme Builder, so the theme must own the header/footer shell, singles, forms, SEO and assets. A block theme would fight Elementor.
- **Elementor Free 4.3.3, classic `\Elementor\Widget_Base` widgets**: the client mandates Elementor. The `REPEATER` and `GALLERY` controls are free and replace ACF Pro for section-level lists. Disable the Atomic Editor so the client sees one editing model. Set Google Fonts off, the FA4 shim off, CSS print method to external, color and typography schemes disabled, and Element Caching, Lazy BG and Inline SVG icons on. Keep only three breakpoints: 767, 1024 and 1366.
- **ACF Free 6.8.10**: field groups registered in PHP (`acf_add_local_field_group`, stable `field_mwm_*` keys) and read through an `mwm_field()` wrapper that falls back to `get_post_meta`. Never use Repeater, Gallery, Options or Flexible Content.
- **Settings API page `mwm_settings`** (one autoloaded option): the single global settings screen. Not the Customizer, not an ACF settings post.
- **Fonts: Alexandria (display, weights 600/700/800) + Readex Pro (body, weights 400/500/600)**: OFL, variable, Fontsource 5.3.0, Arabic and Latin woff2 only. Preload one file. Add a metric-adjusted fallback. Never glyph-subset the Arabic files, because that breaks shaping. Lean fallback: Alexandria only (about 60 KB).
- **Image pipeline: mupdf (lossless raw JPEG copy) → ASCII rename + manifest → `wp media import`**, with `image_editor_output_format` producing JPEG→WebP sub-sizes. Local GD supports WebP and AVIF (verified). Upload originals; never pre-convert.
- **Mail: native form + `wp_mail`**, From `no-reply@domain` and Reply-To the visitor. Use FluentSMTP 2.4.1 in production and Mailpit locally.

### Expected Features

Details are in [FEATURES.md](./FEATURES.md). Competitors are mostly slow, dated, English-first or machine-translated sites with no structured data. They build trust with years, counters and testimonials, which MWM cannot use. MWM should build trust through **verifiable licensing, real field photos, explicit scope, process steps and capability facts**.

**Must have (table stakes):**
- The locked page set: Home, About, Services hub, 3 service detail pages, Gallery, Licenses, Branches, Quote/Site visit, Contact. Add **Privacy policy** (PDPL requires it before collecting form data) and a **404** page.
- A header with phone, WhatsApp and "اطلب عرض سعر"; transparent on hero pages and solid on scroll; an accessible mobile drawer. A footer with CR/VAT and licence numbers that show only when filled. Breadcrumbs.
- **NCEC licensing shown in exactly the two licensed fields**, on the home strip, Licenses page, the matching service pages and the footer. **Never on ventilation.**
- Real PDF field photos with honest Arabic captions and alt text. An industries-served grid, process steps and grounded "why MWM" capability facts. FAQ. National coverage (3 branches, "نعمل في جميع أنحاء المملكة").
- **A floating WhatsApp picker offering both numbers with labels**, `tel:` links in E.164 everywhere, and a **sticky mobile action bar** at ≤767 px that replaces the float.
- **A quote/site-visit form** with name, Saudi mobile, facility name and type, city/region, service (from CPT data), request type (quote / site visit / maintenance contract / spare parts / consultation), preferred contact method, optional email and details. Add a PDPL notice and an unticked consent checkbox stored with a timestamp. Protect it with a nonce, honeypot, time trap and throttle, and provide a no-JS fallback. Show a success state with a WhatsApp CTA.
- Admin side: one «إعدادات الموقع» screen; Service, Service-item, Branch, License, FAQ and Request CPTs; every widget prefilled, with empty fields hidden; Kit globals that mirror the tokens; header and footer as saved Elementor templates picked on the settings page.
- SEO, accessibility and performance: one H1 per page, Latin slugs, OG/Twitter tags, JSON-LD, the core sitemap without users or internal CPTs, WCAG 2.2 AA, reduced motion, Lighthouse mobile ≥90, and click-to-load maps.

**Should have (differentiators, mostly cheap):**
- **"تحقق من الترخيص" links** to the NCEC e-compliance search and document verification (shown only when filled). A **licence↔service badge mapping**. An admin notice when a licence approaches expiry.
- **Page-aware pre-filled WhatsApp text** using `{service}`/`{page}` tokens. **Form presets from URL parameters** (`?service=…&type=maintenance`). Spare-parts and maintenance-contract CTAs on the filters page.
- **Lead attribution** (source page, referrer, UTM, device) and **an inbox workflow**: status, filters, CSV export, and "reply via WhatsApp/call" buttons.
- **Service detail pages editable in Elementor** (an improvement on Al-Masa's PHP-only singles). Deeper widget controls than Al-Masa. An in-dashboard editing guide.
- A KSA coverage SVG map (P2), a company profile PDF download (P2), and click instrumentation through `dataLayer` with an optional GA4/GTM ID (P2; no script loads when the ID is empty).
- Premium but light motion (reveal/stagger, the leaf-pattern "air flow" band) that respects reduced motion.

**Defer (v1.x / v2+):**
- Projects/case studies, testimonials and client logos, which are blocked until real, permissioned data exists. A dedicated spare-parts catalogue page. GA4 with a consent banner. A submitter auto-reply. The English version (strings are translation-ready). A blog. Stats/counters (only with verified numbers).
- **Anti-features, never built:** invented counters; NCEC, Vision 2030 or SGI logos used as decoration; city×service doorway pages; ~20 thin sub-service pages; file uploads on the form; chat plugins or reCAPTCHA by default; autoplay video or heavy hero sliders; popups; a search page.

### Architecture Approach

Details are in [ARCHITECTURE.md](./ARCHITECTURE.md). The theme is a layered monolith: **core helpers → settings → content model (CPT/ACF) → data access (`inc/data/`, pure static-cached reads) → rendering**. In the rendering layer, each Elementor widget and each PHP fallback calls the **same** `template-parts/sections/*` partial with an `$args` array, using `mwm_arg()` semantics: an absent key falls back to live data, and a present-but-empty key hides the element. Theme-owned global chrome (floating contact, skip link, JSON-LD, OG) renders from settings, independent of Elementor. A versioned, idempotent seeder, run with `wp mwm seed`, builds media, records, Kit, templates, pages, service singles and menus from data files and the photo manifest. Folders map 1:1 to the build phases.

**Major components:**
1. **Settings (`inc/settings/`)**: a schema-driven Settings API page holding phone slots (label, number, WhatsApp, call), email, notify recipients, WhatsApp template, company IDs, socials, floating-button options, header/footer template pick and the **page-role registry** (`mwm_page_id('quote')`). It is one source for the header, footer, float, sticky bar, link resolver, shortcodes, form email and schema.
2. **Content model + data layer (`inc/content/`, `inc/data/`)**: `mwm_service` (3 records, public, Elementor-enabled), `mwm_service_item` (about 18, non-public, linked by `post_object`), `mwm_branch`, `mwm_license`, `mwm_faq`, `mwm_request` (private inbox), and a per-service ordered gallery metabox. Templates never run raw `WP_Query`.
3. **Elementor layer (`inc/elementor/`)**: an `Mwm_Widget` base with **declarative content specs** (`copy` / `dynamic` / `media` / `link` / `toggle`) and **style-part traits** (`box`, `text`, `button`, `card`, `media`, `grid`, `icon`) that use CSS custom properties as the control-to-CSS contract. A `link_source` select (custom / page role / WhatsApp 1–2 / call 1–2 / email) means a client never pastes a phone number into a link. It also provides the header-style document control, header/footer resolution with template CSS enqueued in `<head>`, and an asset pre-scan that enqueues per-widget CSS.
4. **Leads (`inc/leads/`)**: shared validation rules (PHP and localized JS), the AJAX handler plus `admin-post` fallback, the inbox CPT with status and unread bubble, notifications, privacy exporter/eraser and retention cleanup.
5. **SEO (`inc/seo/`)**: the JSON-LD `@graph` and OG fallback. It backs off automatically when Yoast or Rank Math is active and keeps the branch LocalBusiness nodes.
6. **Assets and JS**: global `tokens.css` bridged to `--e-global-*` Kit variables with brand fallbacks, plus `base.css`, `core.css` and `motion.css`, and one CSS file per widget. One deferred `theme.js` with a module registry and an idempotent `init(scope)`, re-run on Elementor `element_ready`, using a shared scroll dispatcher and IntersectionObserver and an AbortController per instance.
7. **Seeder (`inc/seed/`, loaded only under CLI or admin) + `tools/extract-pdf-images.mjs`**: entity identity comes from `_mwm_seed_key`, a client-edit hash guard prevents overwriting edits, and element IDs are deterministic. The seeder writes `_elementor_data` with `wp_slash(wp_json_encode())`.

### Cross-research conflicts (resolved — roadmapper should treat these as decisions)

| Topic | Conflict | Decision |
|---|---|---|
| CSS cascade layers | STACK suggests `@layer`; ARCHITECTURE and PITFALLS forbid it | **No `@layer`, no `!important`.** Layered theme CSS loses to all unlayered Elementor CSS. Use single-class BEM selectors and load theme CSS after `elementor-frontend`, so `{{WRAPPER}}` client overrides always win. |
| ACF JSON | STACK says save and load `acf-json/`; PITFALLS says PHP only | **PHP is the only source** (`acf_add_local_field_group`, stable keys, `return_format = id`). No save path, so production UI edits can't diverge from git. |
| Sub-services | FEATURES and PITFALLS suggest hierarchical `mwm_service` children | **A separate non-public `mwm_service_item` CPT** linked by `post_object`. This avoids thin public child URLs and sitemap pollution, matches the "no per-sub-service pages" anti-feature, and still supports per-item photos (PDF pp. 11–17). |
| Gallery storage | FEATURES suggests an attachment taxonomy; ARCHITECTURE a per-service ordered ID metabox | **A per-service ordered attachment-ID metabox** (`mwm_gallery`, the same format as ACF Pro Gallery). It gives curated ordering and needs no migration if Pro is added. The gallery widget pulls by service, with tabs = 3 lines. Revisit the taxonomy only if the client wants to bulk-tag from the Media Library. |
| PDF extraction | ARCHITECTURE and PITFALLS suggest pdf.js/pdfimages + sharp re-encode to WebP before import | **mupdf raw JPEG stream copy (lossless, already probed: 29 of 30 images are DCT)**, ASCII rename plus manifest, import originals, and let WordPress generate WebP sub-sizes. |
| Local WebP/AVIF | PITFALLS says XAMPP lacks them | **Verified on this machine:** GD has WebP and AVIF. The production host must still be checked in Site Health. |
| WhatsApp picker | STACK suggests Popover API; PITFALLS suggests `<dialog>` | **Popover API** for the two-link picker (non-modal, light-dismiss, keyboard, zero JS). **`<dialog>`** for the lightbox, which is modal. |
| Kit binding in controls | STACK and PITFALLS bind controls to Kit globals by default; ARCHITECTURE leaves them empty | **Controls empty by default.** Theme CSS consumes Kit-bridged tokens, so widgets follow the Kit with no per-instance CSS. Use `global => default` only where the binding must be visible (for example the primary button). |
| Element Caching | — | `is_dynamic_content()` returns **true** for any widget that reads CPTs, settings (phones!), post context or nonces; **false** only for purely static copy widgets. |
| Header/footer template pick | Al-Masa used the Customizer | **The MWM settings page** (one place), seeded automatically. |
| LocalBusiness without addresses | STACK emits city-level nodes; PITFALLS says no node without an address | **Organization always**, with `areaServed` plus branch cities. **A LocalBusiness node per branch only once the branch has a real street address.** Never `geo` or hours unless entered. |
| PDPL consent | FEATURES says a required checkbox; PITFALLS says optional | **A notice above the submit button plus a required, unticked consent checkbox** storing `consent_at` and the policy URL. The client's counsel may relax this. |
| PHP floor | PITFALLS says assume 8.1 if unknown | **8.2** (8.1 is EOL). Confirm the host in Phase 1. |

### Critical Pitfalls

Details are in [PITFALLS.md](./PITFALLS.md), which lists 20 pitfalls, each mapped to a phase.

1. **Elementor persisted-schema traps (Pitfalls 3, 5; Architecture anti-pattern 1).** Widget `get_name()`, control IDs, control types and repeater field names are stored in `_elementor_data`. Renaming them loses content, and changing a default silently rewrites untouched pages. TEXT/TEXTAREA defaults are persisted on save, so dynamic defaults freeze. **Avoid it:** use empty defaults plus a `placeholder` showing the live value, with a template fallback **[VERIFY]**. **Freeze widget names and control IDs before seeding.** Never run DB queries in `register_controls()`. Provide editor-only empty states.
2. **Content integrity and photos (Pitfall 12).** Licence badge on ventilation; invented numbers, addresses or coordinates; «شركة» vs «مؤسسة»; "Sonolite Valve"; possible stock or third-party photos; 1280 px sources upscaled into heroes. **Avoid it:** licence badges come only from linked License records. Keep a content ledger (`docs/content-sources.md`). Run a client confirmation checklist before seeding closes. Use split or inset hero layouts sized for ≤1280 px sources. Ask the client for original photos.
3. **Brand contrast (Pitfall 10).** `#5BBF75` on white is 2.29:1 and `#A5D7FC` on white is 1.53:1, so both fail even 3:1 for UI. `#28864B` on `#EAEAEA` is 3.79:1 and fails for body text. **Avoid it:** use token *roles* (text `#194223`/`#3A423C`, link `#28864B` on white only, leaf for decoration only). Leaf buttons get deep-green labels (4.96:1). Focus rings are `#194223` on light backgrounds and `#A5D7FC` on deep green. Include a safe-pairs table in the client guide.
4. **Hero LCP, fonts and asset bloat (Pitfalls 8, 9, 19).** The hero must be a real `<img>` with `eager`/`fetchpriority=high` and must **never** be hidden by reveal or entrance animations. It should be static by default. Self-host the two variable fonts with one preload and a metric fallback. Use per-widget CSS and dequeue block-library and global-styles. Maps load on click only. Measure logged out, on staging, with the mobile preset, taking the median of three runs.
5. **Lost leads and PDPL (Pitfalls 13, 14).** On XAMPP, `wp_mail` "works" silently; Gmail rejects mail without SPF/DKIM; cached pages serve stale nonces; throttles keyed on `REMOTE_ADDR` break behind a proxy. **Avoid it:** the inbox CPT is the source of truth and the `wp_mail_failed` status is recorded. Use SMTP in production with From = the domain. Base anonymous protection on honeypot, time trap and throttle, with a fresh-nonce strategy for cached pages. Make the trusted-proxy IP header configurable and store only a salted hash. Provide a privacy page, exporters/erasers and retention cleanup.

Also enforce these from Phase 1:
- **Rewrite Al-Masa modules; never copy them.** Use a Saudi E.164 phone module and grep for `+2`, `01[0125]` and `almasa`.
- **RTL means logical properties**, with no `left/right` in theme CSS. Wrap phone numbers, emails and Latin terms in `<bdi dir="ltr">`. Handle negative `scrollLeft` in RTL sliders. Use `overflow-x: clip`, not `hidden`.
- **Arabic typography:** `letter-spacing: 0`, `font-synthesis: none`, line-height ≥1.6 for body text, Western digits site-wide.
- **Windows hygiene:** `.gitattributes`/`.editorconfig`, lowercase paths, no BOM, `mysqldump --result-file` (PowerShell `>` writes UTF-16).
- **Deploy:** search-replace both `http://` and `http:\/\/` (JSON-escaped) forms, then Regenerate CSS.

## Implications for Roadmap

The client locked a **horizontal-layers** build order at standard granularity. The research supports **7 phases**. Each phase maps to one or two theme folders, and the dependency chain is strict: settings before chrome, the data model before widgets, the widget framework before any real widget, the widget-schema freeze before seeding, and real content before measurement.

### Phase 1: Foundation & Environment Baseline
**Rationale:** Everything renders through the shell and tokens, and the Elementor and WordPress defaults must be neutralized before any widget CSS is written. Locale, i18n, RTL and phone conventions are cheapest to set at the start.
**Delivers:**
- Dev tooling in `tools/` (WP-CLI shim, Git by full path, Composer, PHPCS/WPCS, Mailpit, Playwright, Lighthouse). `.gitattributes`/`.editorconfig`. Theme + docs + tools versioned. Root `PROJECT_RULES.md` and `.cursor/rules`.
- Elementor and ACF installed and activated, theme `mwm` activated, the existing logo reused as Site Logo (attachment 6, no re-upload).
- Elementor baseline: Google Fonts off, schemes disabled, FA4 shim off, external CSS, Atomic Editor off, 3 breakpoints.
- Theme skeleton (bootstrap module list, setup, image sizes, block-CSS dequeues, `header.php`/`footer.php` shell, base templates).
- Core helpers (`mwm_arg`, Saudi E.164 phone, `wa.me`/`tel:` builders, a `<bdi>` output helper).
- `tokens.css` with role tokens bridged to Kit variables. Self-hosted Alexandria and Readex Pro with `additional_fonts` registration. `base.css`, `core.css`, `motion.css` with reduced-motion handling. A `theme.js` registry skeleton.
- Kick off the **client confirmation checklist** (see Gaps).

**Addresses:** the performance, accessibility and RTL baseline; translation-readiness; design tokens mapped to CSS and the Kit.
**Avoids:** Pitfalls 1, 4, 6, 7, 8, 10, 15, 16, 19 (Al-Masa copy-paste, Kit/Google Fonts/Atomic conflicts, physical CSS, faux bold, font payload, contrast roles, hardcoded URLs, Windows traps, block CSS bloat).

### Phase 2: Global Settings, Content Model & Media Extraction
**Rationale:** The header, footer, float, sticky bar, form, schema and every widget read settings and CPT data. Building those consumers first is how phone numbers end up hardcoded. ACF keys and settings paths become data schema, so define them deliberately.
**Delivers:**
- The schema-driven «إعدادات الموقع» Settings API page: phone slots with labels and WhatsApp/call flags, email, notify recipients, WhatsApp template, company IDs, socials, float options, header/footer template pick, **page-role registry**, default OG image and profile PDF.
- CPTs `mwm_service` (Elementor-enabled, Latin rewrite `services`), `mwm_service_item`, `mwm_branch`, `mwm_license`, `mwm_faq`. ACF local groups in PHP only. The per-service gallery metabox. Admin UX (columns, filters, order, data notes).
- The `inc/data/` query layer.
- `tools/extract-pdf-images.mjs` (mupdf) producing ASCII-named photos and `manifest.json` (page → service line → item → Arabic alt → pixel size → "own photo?" flag). This runs early so resolution limits and photo ownership reach the client before any design depends on them.

**Uses:** Settings API, ACF Free local groups, `register_post_type`, mupdf.
**Implements:** the Settings, Content model and Data access components; architecture Patterns 4, 5 and 8.
**Avoids:** Pitfalls 2 (title lookups), 17 (ACF Free workarounds), 18 (NAP model), 20 (media naming).

### Phase 3: Conversion Layer — Lead Pipeline & Global Chrome
**Rationale:** The site should be navigable and able to convert *before* Elementor work begins. The lead backend is independent of Elementor and can be tested with a raw form. It depends only on settings and the data layer.
**Delivers:**
- The `mwm_request` private inbox: status workflow, filters, unread bubble, CSV export, WhatsApp/call reply buttons, attribution fields.
- Shared validation rules (Saudi mobile and landline, Arabic-Indic digit normalization, Arabic MSA messages) used by both PHP and JS.
- The AJAX handler plus `admin-post` no-JS fallback. Nonce strategy safe for cached pages, honeypot, time trap, and a salted-IP-hash throttle with a configurable trusted-proxy header.
- `wp_mail` notifications (From the domain, Reply-To the visitor, `wp_mail_failed` status) and a local Mailpit mu-plugin.
- PDPL support: consent field, privacy exporter/eraser, retention cron.
- PHP header and footer partials. The floating WhatsApp picker (Popover API, both numbers, labels, page-aware text). The sticky mobile action bar (≤767 px, safe-area aware, hidden while the form has focus).

**Addresses:** the quote form backend, the floating two-number WhatsApp, `tel:` links, the sticky bar, the inbox workflow and lead attribution.
**Avoids:** Pitfalls 1 (Egyptian regex), 11 (focus obscured, target size), 13 (lost leads), 14 (PDPL).

### Phase 4: Elementor Widget Framework (+ proving spike)
**Rationale:** Retrofitting style controls across about 15 widgets is the most expensive rework in Al-Masa's pattern. The base class must be complete, and proven on real widgets, before scaling. Several Elementor internals are **[VERIFY]** and must be spiked against the installed 4.3.3.
**Delivers:**
- The «MWM» category. The `Mwm_Widget` base with content-spec and style-part traits, `link_source` resolver and shortcodes (`[mwm_phone]`, `[mwm_wa]`, `[mwm_email]`).
- A document control for header style (auto / transparent / solid). Header/footer resolution from saved templates with **head-time template CSS**. The per-widget asset pre-scan.
- A hardened JS registry (AbortController, shared observers; reveal forced on and autoplay off in the editor).
- Proof on 2 widgets: page-hero (dynamic fields) and services (data-fed grid/slider).
- Spike results recorded for:
  - whether a `placeholder` value avoids persistence (check `_elementor_data` after saving)
  - `Core\Files\CSS\Post::create()->enqueue()`
  - `elementor/fonts/additional_fonts` with the `elementor_google_font=0` option
  - the Atomic Editor disable mechanism
  - Kit container defaults and whether they overflow at 360 px

**Implements:** architecture Patterns 1–3, 7, 11 and 12.
**Avoids:** Pitfalls 3, 4 and 5 (schema traps, Kit fighting the theme, editor preview breakage).

### Phase 5: Section Widgets & Service Single Template
**Rationale:** Build the full widget set on the proven framework, ordered by consumer: chrome widgets first (seeded templates and every page need them), then content widgets, then data-heavy widgets, then the form front end.
**Delivers:**
- **Chrome widgets:** header, footer, CTA band, contact info.
- **Content widgets:** hero (static `<img>` LCP, optional pausable slider with a visible pause control), page-hero, intro, services, service-items, industries (repeater), why-MWM (repeater), process steps (repeater), FAQ.
- **Data widgets:** gallery (service tabs, `<dialog>` lightbox, paginated), licenses (verify link, expiry awareness, service badge), branches (click-to-load map facade only when `map_query` is set), an optional KSA coverage SVG.
- **Quote form widget:** URL presets, PDPL notice, success state with WhatsApp.
- `single-mwm_service.php`: Elementor content when built, otherwise a PHP section map selected by role.
- Headings default to `h2`; only the hero and page-hero default to `h1`. Typography controls hide letter-spacing.
- **Ends with a frozen, documented widget/control schema** in `PROJECT_RULES.md`.

**Addresses:** every page section, the licence↔service mapping, spare-parts and maintenance CTAs, deeper-than-Al-Masa controls.
**Avoids:** Pitfalls 6 (RTL sliders and icons), 9 (hero LCP), 10 (control defaults), 11 (2.2.2 pause controls, dialogs), 12 (badges only via linked records).

### Phase 6: Content, Media & Page Seeding
**Rationale:** The seeder writes Elementor JSON that depends on frozen widget names and control IDs, and it consumes the Phase 2 manifest. Real content is a prerequisite for meaningful SEO and performance measurement.
**Delivers:**
- The versioned, per-entity idempotent `wp mwm seed`, plus an admin Tools fallback. Steps in order: options → media import (manifest, Arabic alt) → licences, services, all ~18 PDF sub-items, branches, FAQs, galleries → Kit globals (fixed `_id`s, full arrays) → header/footer templates → the page set with roles and the front page → the 3 service singles → menus → rewrite flush and Elementor cache clear.
- Latin slugs (`rehabilitation`, `pollution-control-filters`, `ventilation-systems`, `quote`, …).
- Grounded MSA copy kept in `inc/seed/data/`. A content ledger. A privacy page draft.
- Every optional field empty and hidden until the client fills it.
- Client checklist answers applied: entity type, Solenoid spelling, photo ownership, number labels.

**Addresses:** "all service content and photos from the PDF used", reproducible staging and production builds.
**Avoids:** Pitfalls 12 (integrity), 17 (seed by ACF key), 18 (slugs), 20 (duplicate media), Architecture anti-pattern 6.

### Phase 7: SEO, Performance, Accessibility, QA & Handoff
**Rationale:** Schema output, Lighthouse and axe results are only meaningful on real seeded content. QA verifies the cross-cutting acceptance targets.
**Delivers:**
- **SEO:** the JSON-LD `@graph` (Organization; LocalBusiness only for branches with addresses; Service; BreadcrumbList). OG/Twitter tags with a 1200×630 default. Sitemap filters (no users, no `post`, internal CPTs excluded). Author archives off. SEO-plugin back-off. Validated with Rich Results Test and the Schema.org validator.
- **Performance:** confirm the pre-scan works, `srcset` and sizes, the single preload, metric fallback tuning, and Elementor feature checks. Target Lighthouse mobile ≥90, measured on staging logged out with a 3-run median.
- **Accessibility:** WCAG 2.2 AA, with an axe pass and a keyboard walkthrough at 390 px.
- **Responsive QA:** Playwright screenshots at 1920→360 with horizontal-overflow assertions. Fresh-DB editor test and a test of 10 consecutive control changes.
- **Handoff:** the hide-when-empty audit, the "Looks Done But Isn't" checklist, the dashboard editing guide with a safe color pairs table, a deploy runbook (dual search-replace, Regenerate CSS, cache exclusions, SMTP and SPF/DKIM test to Gmail, file-case check script, utf8mb4 dump).

**Addresses:** Lighthouse 90+, local SEO, WCAG AA, responsive and editability QA.
**Avoids:** Pitfalls 8, 9, 10, 11, 15, 16, 18, 19.
**Optional split if the plan grows:** 7a "SEO/perf/a11y hardening" and 7b "QA, deploy readiness & handoff", which stays within 5–8 phases.

### Phase Ordering Rationale

- **Settings → chrome → widgets** prevents hardcoded contact data. Every consumer reads `mwm_settings` through `mwm_setting()` or `link_source`.
- **Content model before widgets and the form.** Form selects, WhatsApp `{service}` tokens, gallery tabs, licence badges and schema all derive from CPT data. The ACF keys become the persisted schema.
- **Framework before widgets, and a schema freeze before seeding.** Al-Masa's biggest rework and data-loss risks are retrofitting controls and renaming IDs after content exists.
- **Photo extraction early (Phase 2), seeding late (Phase 6).** The 1280 px resolution ceiling and ownership questions must reach the client before hero layouts are designed, but the seeded JSON needs frozen widgets.
- **Measurement last but designed in from the start.** Each perf and a11y decision (static hero, no reveal on the first section, per-widget CSS, contrast roles, popover/dialog) is made in Phases 1, 4 and 5 and only *verified* in Phase 7.
- **The client confirmation checklist starts in Phase 1 and gates the close of Phase 6.**

### Research Flags

Phases likely needing deeper research during planning (`/gsd-plan-phase --research-phase <N>`):
- **Phase 4 (Widget framework):** **needs a spike.** These are verified only against the 4.3.2 source and docs: TEXT/TEXTAREA default persistence vs `placeholder`; `Core\Files\CSS\Post::create()->enqueue()` for head-time template CSS; whether widget style deps print in `<head>` for documents rendered mid-body; the `additional_fonts` "system" mapping plus `elementor_google_font`; the Atomic Editor disable option; Kit container defaults; the `elementor/documents/register_controls` injection point.
- **Phase 6 (Seeding):** Kit `_elementor_page_settings` structure on 4.3.3 (the default kit may lack `system_colors` until saved); the Elementor container JSON shape; the `elementor_library` section template plus taxonomy term; the hash guard design.
- **Phase 7 (SEO/perf):** the LocalBusiness gating policy and Organization `areaServed` markup without addresses; whether Lighthouse ≥90 is achievable with Elementor's jQuery runtime as the floor (the fallback ladder is in STACK.md); a cache-safe nonce strategy for the production host's cache plugin.

Phases with standard patterns (skip research-phase):
- **Phase 1:** Fontsource, the WP-CLI phar and Elementor settings are all verified. Only confirm Elementor options after install.
- **Phase 2:** CPTs, ACF local groups and the Settings API are standard. mupdf extraction is already probed (30 XObjects, 29 lossless JPEGs).
- **Phase 3:** the form/inbox pattern is proven in Al-Masa. Rewrite it for Saudi phones and harden it.
- **Phase 5:** the widget→template-part pattern is proven. Scroll-snap, `<dialog>` and Popover are Baseline web platform features.

## Confidence Assessment

| Area | Confidence | Notes |
|------|------------|-------|
| Stack | HIGH | Versions checked in wordpress.org, npm, GitHub and Packagist on 2026-10-04 and inspected on this machine (WP 7.1.1, PHP 8.3.35, GD WebP/AVIF). Elementor API names read from 4.3.2 source. Fonts measured. PDF probed. The font aesthetic match and the Atomic-off call are MEDIUM (judgement). |
| Features | MEDIUM-HIGH | Competitor patterns observed on live sites. NCEC licence format and portals, PDPL Art. 11/12, Google schema guidance and Elementor Free limits come from primary sources. Conversion-impact claims are industry consensus, not measured. The WhatsApp 92.7% figure is secondary. |
| Architecture | HIGH (baseline) / MEDIUM ([VERIFY] items) | The baseline was read directly from the delivered Al-Masa code, and the ten changes target defects seen in that code. Default persistence, head CSS enqueue, the fonts filter and the Kit meta quirk need a Phase 4 spike. |
| Pitfalls | MEDIUM-HIGH | Al-Masa defects are first-party. Contrast ratios were calculated. Elementor, SDAIA and web.dev are official. Logo-use restrictions, the long-term v3 widget support horizon and Kit container defaults are unconfirmed. |

**Overall confidence:** HIGH. The approach is proven and the versions are verified. The remaining uncertainty is concentrated in a handful of Elementor internals (de-risked by the Phase 4 spike) and in client-supplied facts (handled with hide-when-empty).

### Gaps to Address

- **Client confirmation checklist.** Start it in Phase 1; it must be resolved before Phase 6 closes:
  - legal entity type «مؤسسة» vs «شركة», checked against the CR (it drives copy and schema `legalName`)
  - "Sonolite" → **Solenoid** Valve spelling, and the valve brands
  - which PDF photos are MWM's own work (exclude stock or third-party images), and **original high-resolution photos** (the PDF maxes out at 1280×960, and pages 9–17 at ≤1040 px)
  - licence numbers, class, issue and expiry dates, certificate scans and NCEC verification codes
  - branch addresses, national short addresses, hours, which branch is HQ, and **labels for the two WhatsApp numbers** (department or branch)
  - FAQ answers (maintenance terms, coverage outside the 3 cities, whether MWM prepares plans or only executes NCEC-approved ones)
  - CR, VAT, unified number, social accounts, and whether a separate company-profile PDF exists
  - whether the form should accept toll-free and unified numbers
  - Ship unknown items as empty fields that hide when empty.
- **Production host facts.** PHP and MariaDB/MySQL versions, AVIF support in Site Health, which cache plugin is used (LiteSpeed or Cloudflare APO affects nonce and JS-delay exclusions), domain mailbox and SMTP credentials, and SPF/DKIM on the domain (the From address must be on the domain, not `@gmail.com`). Collect these in Phase 1 and act on them in Phase 7.
- **Elementor [VERIFY] items.** Spike in Phase 4 against the installed 4.3.3 and record the results in `PROJECT_RULES.md` before scaling widgets. Fallbacks: if the head CSS pre-scan proves fiddly, ship one minified bundle; if `placeholder` doesn't prevent persistence, keep the explicit "follow dashboard" switcher.
- **Lighthouse budget.** Elementor's frontend (jQuery runtime) is an irreducible floor. If mobile scores below 90 after Phase 5, follow STACK.md's ladder: drop Readex Pro, dequeue eicons, confirm Element Caching and Lazy BG are on and only 3 breakpoints are active.
- **Government marks.** Assume no NCEC, Vision 2030 or SGI logos; use text plus the client's own certificate. Confirm with the client if they insist.
- **PDPL specifics.** Consent wording, retention period and cross-border transfer (Gmail, non-KSA hosting) need the client's counsel. The theme provides the mechanisms; this is not legal advice.
- **Gallery storage choice.** The recommendation is a per-service ordered metabox. Confirm in Phase 2 planning that the client doesn't need bulk tagging from the Media Library (an attachment taxonomy would be the alternative).

## Sources

### Primary (HIGH confidence)
- wordpress.org plugin API (2026-10-04): Elementor 4.3.3, FluentSMTP 2.4.1, WP Mail SMTP 4.10.0, Rank Math 1.0.279, Yoast 28.6, Performance Lab / Modern Image Formats. Elementor GitHub `changelog.txt`. advancedcustomfields.com changelog (ACF 6.8.10 security release).
- On-machine inspection: `wp-includes/version.php` (7.1.1), `php -v` (8.3.35), GD info (WebP/AVIF), and the Elementor 4.3.2 source on the Al-Masa install (`Global_Colors`, `Global_Typography`, `kits_manager`, `Document::update_settings`, `additional_fonts`, `print_google_fonts`, option names, Swiper v8 handle, jQuery dependency).
- Al-Masa theme code and `PROJECT_RULES.md`, read directly. They are the source of the 15 catalogued defects and the proven patterns.
- developers.elementor.com: widget structure, dependencies, output caching (`is_dynamic_content`), inner wrapper, global styles (`__globals__`); "Free does not support active dynamic tags".
- make.wordpress.org hosting handbook (PHP matrix), core posts on AVIF / `image_editor_output_format` / `sizes="auto"`.
- npm registry and GitHub releases: Fontsource 5.3.0 (woff2 sizes measured), mupdf 1.28.1, Playwright, Lighthouse, Lightning CSS, sharp, WP-CLI 2.12.0, Composer, PHPCS 4.0.4 vs WPCS 3.4.1 (`^3.13.5`), Mailpit.
- NCEC e-compliance licence search and Document Verification portals; Kayan and EPCA licence formats (`ELESL-YYYY-NNNNNN`).
- SDAIA PDPL, Implementing Regulation (Art. 11 consent, Art. 12 notice), Privacy Policy Guideline.
- Google Search Central (Organization, LocalBusiness with `parentOrganization`). Rank Math KB (multi-location is PRO-only).
- MuPDF.js probe of `docs/source/services.pdf`: 30 image XObjects, 29 DCT JPEG, max 1280×960 (one 1600×1200).
- WCAG 2.x relative-luminance contrast ratios calculated from brand hex values.

### Secondary (MEDIUM confidence)
- Elementor v4 FAQ and 4.0 developers update: Atomic Editor on by default for new installs, legacy widgets coexist and can be disabled.
- Elementor issue tracker: Kit `_id` → `--e-global-color-{_id}`, default-kit `system_colors` quirk, `_elementor_data` stores non-defaults, editor `removeDefault` keeps TEXT/TEXTAREA values.
- Competitor sites: aftsaudi.com, getsa.net, saudi-ventilation.com, smaartfan.com, opensooq listing.
- Saudi Internet Report 2025 via GMCSCO (WhatsApp 92.7% usage). Arabic web-font `unicode-range` guidance (voctos.net, sultanbyte.com). caniuse / webstatus for scroll-driven animations.
- Gmail/DMARC alignment behavior for a spoofed `From: @gmail.com` (industry knowledge).

### Tertiary (LOW confidence)
- `@view-transition` cross-document support details (not verified; optional enhancement only).
- Exact restrictions on NCEC, Vision 2030 and SGI logo use; the long-term support horizon for Elementor v3 custom widgets; Kit container default padding and gap on 4.3.3. Validate in Phases 4 and 6 or with the client.

---
*Research completed: 2026-10-04*
*Ready for roadmap: yes*
