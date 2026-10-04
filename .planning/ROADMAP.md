# Roadmap: MWM — Environmental Services Website (mwmpower-ksa)

## Overview

The build follows the client's horizontal-layers order: first neutralize WordPress and Elementor defaults and lay down the RTL shell, design tokens and self-hosted fonts. Then define the global settings, the CPT/ACF content model and the extracted PDF photo library, so nothing downstream hardcodes contact data. Next, the site becomes able to convert (lead pipeline, inbox, floating WhatsApp, sticky bar, PHP chrome) before any Elementor work. A proven Elementor widget framework, spiked against the installed 4.3.3, comes before the full «MWM» section widget set and the service single template, which ends with a frozen widget/control schema. An idempotent seeder then builds the real pages, records, media and menus from grounded content. The last phase verifies SEO, Lighthouse 90+, WCAG 2.2 AA and responsive/editability QA on real content, and hands the site over with an Arabic editing guide and a deploy runbook.

## Phases

**Phase Numbering:**
- Integer phases (1, 2, 3): Planned milestone work
- Decimal phases (2.1, 2.2): Urgent insertions (marked with INSERTED)

Decimal phases appear between their surrounding integers in numeric order.

- [ ] **Phase 1: Foundation & Environment Baseline** - Theme `mwm` active on Elementor/ACF Free with RTL shell, brand tokens bridged to the Kit, self-hosted fonts and dev tooling
- [ ] **Phase 2: Global Settings, Content Model & Media Extraction** - One «إعدادات الموقع» screen, page roles, service/branch/license/FAQ records and the extracted PDF photo manifest
- [ ] **Phase 3: Conversion Layer — Leads & Global Chrome** - Quote/site-visit form, private inbox, floating WhatsApp picker, sticky mobile bar and settings-fed PHP header/footer
- [ ] **Phase 4: Elementor Widget Framework (+ proving spike)** - «MWM» widget base with full content/style controls, link sources, shortcodes and template header/footer, proven on page-hero and services
- [ ] **Phase 5: Section Widgets & Service Single Template** - Every page section as an editable «MWM» widget plus the service detail template, ending with a frozen widget/control schema
- [ ] **Phase 6: Content, Media & Page Seeding** - Idempotent `wp mwm seed` builds the full Arabic page set, records, photos, Kit, templates and menus from grounded content
- [ ] **Phase 7: SEO, Performance, Accessibility, QA & Handoff** - JSON-LD/OG/sitemap, Lighthouse 90+, WCAG 2.2 AA, 1920→360 QA, editing guide and deploy runbook

## Phase Details

### Phase 1: Foundation & Environment Baseline
**Goal**: The custom `mwm` theme runs on the client's existing WordPress install with Elementor Free and ACF Free, rendering a true RTL Arabic shell whose colors and fonts come from brand tokens bridged to the Elementor Kit, with dev tooling and project rules in place.
**Depends on**: Nothing (first phase)
**Requirements**: FND-01, FND-02, FND-03, FND-04, FND-05, FND-06, FND-07, FND-08
**Success Criteria** (what must be TRUE):
  1. Visiting `http://localhost/mwmpower-ksa` shows the `mwm` theme with the client's existing logo (attachment 6) and Site Icon (attachment 9); Elementor Free and ACF Free are active, the locale is still `ar`, permalinks are still `/%postname%/`, and no media was re-uploaded.
  2. The page renders `dir="rtl"` with a mirrored-by-design layout (logical CSS properties only). Phone numbers, emails and Latin terms inside Arabic text display in the correct order.
  3. Changing a global color or font in the Elementor Kit restyles the theme's shell. Network requests show Alexandria and Readex Pro served locally as woff2 with one preload, and no request to Google Fonts.
  4. Elementor settings show Google Fonts off, default schemes disabled, FA4 shim off, external CSS print, Atomic Editor off and breakpoints 767 / 1024 / 1366, all applied from code.
  5. `PROJECT_RULES.md`, `.cursor/rules`, `.gitattributes`/`.editorconfig` exist, and WP-CLI, PHPCS+WPCS, Playwright, Lighthouse and Mailpit run from `tools/`. A PHPCS/grep check finds no hardcoded marketing copy, phones or URLs and no untranslatable strings outside text domain `mwm`.
**Plans**: TBD
**UI hint**: yes

### Phase 2: Global Settings, Content Model & Media Extraction
**Goal**: The admin manages all company-wide data on one settings screen and all repeatable business data (services, sub-services, branches, licenses, FAQs) as structured records, and every PDF field photo is extracted at native resolution and catalogued, ready for consumers.
**Depends on**: Phase 1
**Requirements**: SET-01, SET-03, SET-05, DATA-01, DATA-02, DATA-03, DATA-04, DATA-05, DATA-06, MEDIA-01, TRUST-04
**Success Criteria** (what must be TRUE):
  1. Admin opens «إعدادات الموقع» and edits phone slots (label, number, WhatsApp/call flags), email, notification recipients, WhatsApp template, CR/VAT/unified number, socials, profile PDF, default share image and analytics ID in one place. A local number like `0573825004` is stored and resolved as `+966573825004` for links while displayed in local format.
  2. Admin assigns page roles (home, about, services, gallery, licenses, branches, quote, contact, privacy); renaming an assigned page does not break role-based links.
  3. Admin creates and orders service records (with a public Latin URL under `/services/` that opens in Elementor and falls back to PHP), sub-service items (no public page), branches, licenses and FAQs from the dashboard, with ACF fields registered in PHP under stable keys.
  4. When a license's expiry date is within the configured number of days, the admin sees a dashboard notice.
  5. `tools/extract-pdf-images.mjs` produces about 29 losslessly extracted, ASCII-named photos plus a manifest (page → service line → sub-item → Arabic alt → pixel size → own-photo flag) that the client can review for resolution and ownership.
**Plans**: TBD
**UI hint**: yes

### Phase 3: Conversion Layer — Leads & Global Chrome
**Goal**: A visitor can reach MWM from any page by WhatsApp (either number), call or a quote/site-visit request that is never lost, and the admin works incoming leads from a private inbox, all fed from the global settings before any Elementor work.
**Depends on**: Phase 2
**Requirements**: SET-02, SET-06, LEAD-01, LEAD-02, LEAD-03, LEAD-04, LEAD-05, LEAD-06, LEAD-07, LEAD-08, LEAD-09, LEAD-10, LEAD-11, LEAD-12
**Success Criteria** (what must be TRUE):
  1. Visitor opens the floating WhatsApp button, picks one of the two labelled numbers, and lands in a chat pre-filled with the current page/service. On a ≤767 px screen a safe-area-aware sticky bar (WhatsApp / call / quote) appears and hides while a form field is focused.
  2. Visitor submits the quote/site-visit form (with JavaScript on or off), sees Arabic inline validation, the PDPL notice and an unticked required consent box, and then a success state with a WhatsApp shortcut. Opening `/quote/?service=…&type=maintenance` preselects service and request type. Spam submissions (honeypot, too-fast, throttled IP) are rejected.
  3. Every submission appears in the private dashboard inbox with source page, referrer, UTM and device, even if email fails. The admin receives an email (domain From, visitor Reply-To, visible in Mailpit locally), and failed sends are flagged.
  4. Admin filters requests by status (new / contacted / closed), sees an unread count, replies with one click via WhatsApp or call, exports to CSV, and can export or erase a person's data through WordPress privacy tools. Old requests are purged after the configured retention period.
  5. Changing a phone, email or social value in settings immediately updates the PHP header, footer, floating button, sticky bar and form notifications. Empty optional fields (socials, CR/VAT, license numbers, addresses, profile PDF) leave no trace in the UI.
**Plans**: TBD
**UI hint**: yes

### Phase 4: Elementor Widget Framework (+ proving spike)
**Goal**: A complete, spike-proven «MWM» widget framework exists, so any section widget gets full content and style controls, safe link sources, editor-preview support and live-data prefill for free, demonstrated on two real widgets and on template-driven header/footer.
**Depends on**: Phase 3
**Requirements**: SET-04, EDIT-02, EDIT-03, EDIT-04, EDIT-05, EDIT-07, SECT-04, SECT-05
**Success Criteria** (what must be TRUE):
  1. In the Elementor panel the «MWM» category shows the page-hero (with breadcrumbs) and services widgets. Each exposes typography, colors, background, border/radius, spacing, responsive columns/gaps and alignment controls from one shared framework.
  2. Editor drops the services widget on a page without touching its fields, saves, then edits a service record in the dashboard; the page shows the updated data. Clearing a field in the widget hides that element. Results of the default-vs-placeholder spike are recorded in `PROJECT_RULES.md`.
  3. Editor sets a button's link source to custom URL, page role, WhatsApp 1–2, call 1–2 or email without typing a phone number. `[mwm_phone]`, `[mwm_wa]` and `[mwm_email]` render settings values inside Elementor's own widgets.
  4. Widgets render and re-initialize correctly inside the Elementor editor preview (with helpful empty states), and sliders/reveal keep working after repeated control changes.
  5. Admin picks header and footer Elementor templates on the settings screen and the site uses them, with their CSS loaded in `<head>`. With no template picked, or Elementor deactivated, the PHP header/footer still render.
**Plans**: TBD
**UI hint**: yes
**Research flag**: needs research/spike (`/gsd-plan-phase --research-phase 4`): TEXT/TEXTAREA default persistence vs `placeholder`, `Core\Files\CSS\Post::create()->enqueue()` head-time CSS, widget style deps for mid-body documents, `additional_fonts` + `elementor_google_font`, Atomic Editor disable, Kit container defaults at 360 px, `elementor/documents/register_controls` injection point.

### Phase 5: Section Widgets & Service Single Template
**Goal**: Every section of every page exists as a fully editable «MWM» widget in the "clean air" visual language, including the quote form, gallery, licenses and branches, and service detail pages render from Elementor or a PHP fallback; the widget/control schema is frozen before content is seeded.
**Depends on**: Phase 4
**Requirements**: EDIT-01, EDIT-06, SECT-01, SECT-02, SECT-03, SECT-06, SECT-07, SECT-08, SECT-09, SECT-10, SECT-11, SECT-12, SECT-13, SECT-14, SECT-15, SECT-16, MEDIA-04, TRUST-02, TRUST-03, DSGN-01, DSGN-02, A11Y-03
**Success Criteria** (what must be TRUE):
  1. Editor builds a complete page from «MWM» widgets alone: header (transparent on hero pages → solid on scroll, accessible mobile drawer), home hero (static LCP-safe image with slogan and CTAs; optional slider with a visible pause control), sub-service items, industries, process steps, why-MWM, FAQ accordion, CTA band, contact info, profile PDF button and footer with the leaf-pattern band. Every text, image, link, toggle and repeater item is editable and prefilled from live data.
  2. Visitor filters the gallery by service-line tabs and opens photos in an accessible `<dialog>` lightbox. The licenses section shows issuer, field, number and «تحقق من الترخيص» only when filled. A «مرخّص» badge appears on a service only when a license record links to it. Branches show Jazan, Riyadh, Dammam and Kingdom-wide coverage, with a click-to-load map only when a location is set.
  3. The quote form widget renders the Phase 3 form with URL presets, PDPL notice and success state, and a service detail page renders either its Elementor layout or the PHP section fallback.
  4. Pages show the light "clean air" look (white/off-white surfaces, deep-green sections, sky-blue air accents, logo clear space respected), with subtle transform/opacity reveal and hover motion. The first viewport is never animated in, `prefers-reduced-motion` disables non-essential motion, and ≤1280 px source photos are never upscaled into soft full-bleed images.
  5. All widget names and control IDs are frozen and documented in `PROJECT_RULES.md`.
**Plans**: TBD
**UI hint**: yes

### Phase 6: Content, Media & Page Seeding
**Goal**: One idempotent command builds the complete Arabic site (photos, records, Kit, templates, pages, service singles, menus) from grounded content, with the client's confirmations applied and every claim traceable to a source.
**Depends on**: Phase 5
**Requirements**: MEDIA-02, MEDIA-03, PAGE-01, PAGE-02, PAGE-03, PAGE-04, PAGE-05, TRUST-01, TRUST-05
**Success Criteria** (what must be TRUE):
  1. Running `wp mwm seed` on a fresh DB produces الرئيسية، من نحن، خدماتنا، 3 service detail pages، معرض الأعمال، التراخيص والاعتمادات، الفروع، طلب عرض سعر / معاينة، تواصل معنا، سياسة الخصوصية and 404, built with «MWM» widgets on short Latin slugs (e.g. `/services/rehabilitation/`, `/quote/`), plus header/footer templates, menus, Kit globals and page roles.
  2. Running the seeder again creates no duplicate media, records or pages and does not overwrite anything the client edited.
  3. Visitor finds all three service lines, about 18 sub-items, licensing statements, spare parts and maintenance contracts from the brief and services PDF in polished Arabic, each illustrated with its matching PDF photos (Arabic alt, WebP sub-sizes), and galleries grouped by the three service lines.
  4. NCEC licensing appears on the home strip, licenses page, the rehabilitation and filters service pages and the footer, and never on ventilation. No invented numbers, years, clients, projects or coordinates appear; `docs/content-sources.md` traces every claim.
  5. The client confirmation checklist (entity type, Solenoid spelling, photo ownership/originals, license data, branch addresses, number labels, FAQ answers, CR/VAT/socials) is tracked and its answers are applied, with unknowns shipped as empty hidden fields.
**Plans**: TBD
**UI hint**: yes
**Research flag**: needs research (`/gsd-plan-phase --research-phase 6`): Kit `_elementor_page_settings` structure on 4.3.3 (default kit may lack `system_colors` until saved), Elementor container JSON shape, `elementor_library` section template + taxonomy term, client-edit hash guard design.

### Phase 7: SEO, Performance, Accessibility, QA & Handoff
**Goal**: On real seeded content, the site is verified fast (Lighthouse mobile 90+), findable (local SEO, schema, OG, sitemap), accessible (WCAG 2.2 AA) and solid at every width, and the client can edit and deploy it without a developer.
**Depends on**: Phase 6
**Requirements**: SEO-01, SEO-02, SEO-03, SEO-04, SEO-05, PERF-01, PERF-02, PERF-03, PERF-04, A11Y-01, A11Y-02, A11Y-04, HAND-01, HAND-02
**Success Criteria** (what must be TRUE):
  1. Every page has one H1, semantic headings, a unique title and meta description, and Open Graph/Twitter tags with a 1200×630 default image. Rich Results Test validates the Organization, Service and BreadcrumbList graph, with LocalBusiness only for branches that have a street address. The sitemap excludes users and internal CPTs, author archives are off, and analytics loads only when an ID is set, pushing `dataLayer` events on contact clicks.
  2. Lighthouse mobile scores ≥ 90 on home and a service page (logged out, median of 3 runs), with CLS < 0.1. The hero is an eager, high-priority `<img>`, other images lazy-load with srcset/sizes, and only the CSS/JS of widgets used on a page loads.
  3. A keyboard-only visitor can reach everything via a skip link with visible, never-obscured focus. Every text/UI color pair passes WCAG 2.2 AA, and an axe pass reports no violations.
  4. Playwright screenshots at 1920 / 1440 / 1366 / 1280 / 1024 / 768 / 430 / 390 / 375 / 360 show no horizontal overflow, and the fresh-DB editor test plus 10 consecutive control changes leave every widget working.
  5. The client has an Arabic dashboard editing guide (pages, widgets, settings, records, safe color pairs table) and a deploy runbook (dual search-replace, Regenerate CSS, SMTP + SPF/DKIM test, cache exclusions, utf8mb4 dump, file-case check).
**Plans**: TBD
**UI hint**: yes
**Research flag**: needs research (`/gsd-plan-phase --research-phase 7`): LocalBusiness gating and Organization `areaServed` without addresses, Lighthouse ≥ 90 feasibility with Elementor's jQuery runtime (fallback ladder in STACK.md), cache-safe nonce strategy for the production host's cache plugin.

## Progress

**Execution Order:**
Phases execute in numeric order: 1 → 2 → 3 → 4 → 5 → 6 → 7

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 1. Foundation & Environment Baseline | 0/TBD | Not started | - |
| 2. Global Settings, Content Model & Media Extraction | 0/TBD | Not started | - |
| 3. Conversion Layer — Leads & Global Chrome | 0/TBD | Not started | - |
| 4. Elementor Widget Framework (+ proving spike) | 0/TBD | Not started | - |
| 5. Section Widgets & Service Single Template | 0/TBD | Not started | - |
| 6. Content, Media & Page Seeding | 0/TBD | Not started | - |
| 7. SEO, Performance, Accessibility, QA & Handoff | 0/TBD | Not started | - |
