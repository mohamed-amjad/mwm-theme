# Requirements: MWM — Environmental Services Website (mwmpower-ksa)

**Defined:** 2026-10-04
**Core Value:** A facility owner in Saudi Arabia lands on the site from any device, immediately understands that MWM is an NCEC-licensed air-quality and remediation expert, and can reach them (WhatsApp / call / quote request) in one tap — while the client can change any content themselves from Elementor without a developer.

> Scope note: the client cancelled the optional-features questionnaire and asked the orchestrator to proceed at its discretion; research-recommended differentiators were included, the KSA coverage map deferred. Client may adjust at review.

## v1 Requirements

### Foundation (FND)

- [ ] **FND-01**: Elementor Free 4.3.x and ACF Free 6.8.x are installed and active; the custom theme `mwm` is active; WordPress is not reinstalled (client install, locale `ar`, `/%postname%/` kept)
- [ ] **FND-02**: The client's already-uploaded logo (attachment 6) is the Site Logo and the existing Site Icon (attachment 9) is kept — no duplicate uploads
- [ ] **FND-03**: Elementor baseline is applied from code: Google Fonts off, default color/typography schemes disabled, FA4 shim off, external CSS print, Atomic Editor off, breakpoints aligned with theme CSS (767 / 1024 / 1366)
- [ ] **FND-04**: Visitor sees a true RTL layout built with logical CSS properties only (no flipped LTR); phone numbers, emails and Latin terms render correctly inside Arabic text
- [ ] **FND-05**: Brand tokens (7 identity colors as role tokens, spacing, radius, type scale) exist as CSS variables bridged to Elementor Kit global colors and fonts, so changing a Kit global restyles the whole site
- [ ] **FND-06**: Alexandria (headings) and Readex Pro (body) are self-hosted variable woff2 with `font-display: swap`, one preload, metric fallback; Elementor lists them and never loads Google Fonts
- [ ] **FND-07**: All theme strings use text domain `mwm` and are translatable (English-ready); no hardcoded visible marketing copy, URLs, phones or image URLs in PHP/JS/CSS
- [ ] **FND-08**: Root `PROJECT_RULES.md`, `.cursor/rules`, `.gitattributes`/`.editorconfig` and dev tooling (WP-CLI, PHPCS+WPCS, Playwright, Lighthouse, Mailpit) are in place and documented

### Global Settings (SET)

- [ ] **SET-01**: Admin edits all company-wide data on one «إعدادات الموقع» screen: phone slots (label, number, WhatsApp on/off, call on/off), email, notification recipients, WhatsApp message template, CR / VAT / unified number, social links, company profile PDF, default share image, analytics ID
- [ ] **SET-02**: Changing a phone/email/social value in settings updates header, footer, floating button, sticky bar, CTAs, form notifications and schema everywhere at once
- [ ] **SET-03**: Admin assigns page roles (home, about, services, gallery, licenses, branches, quote, contact, privacy) so internal links never break when a page is renamed
- [ ] **SET-04**: Admin picks the header and footer Elementor templates on the settings screen; PHP fallback header/footer render when none is picked or Elementor is off
- [ ] **SET-05**: Saudi phone numbers are normalized to E.164 (`+9665…`) for `tel:` and `wa.me` links while displayed in local format
- [ ] **SET-06**: Every optional field (social, CR/VAT, license numbers, addresses, profile PDF, analytics) hides its UI completely when empty

### Content Model (DATA)

- [ ] **DATA-01**: Admin manages the 3 main services as public records (title, summary, featured image, icon, licensed flag via linked license, ordered gallery), each with an Elementor-editable detail page and a PHP fallback
- [ ] **DATA-02**: Admin manages sub-service items (title, short description, image, parent service, order) without them becoming thin public pages
- [ ] **DATA-03**: Admin manages branches (city/region, address, national short address, map query, phones, hours, HQ flag, order)
- [ ] **DATA-04**: Admin manages licenses (issuer, field, number, class, issue/expiry date, certificate image, verification URL, linked services)
- [ ] **DATA-05**: Admin manages FAQs (question, answer, order, optional service link)
- [ ] **DATA-06**: ACF field groups are registered in PHP with stable keys; templates read data only through a data-access layer (no ad-hoc queries in templates)

### Media (MEDIA)

- [ ] **MEDIA-01**: All ~29 photos from the services PDF are extracted losslessly at native resolution (not page screenshots), renamed to ASCII, and catalogued in a manifest (page → service → sub-item → Arabic alt → size)
- [ ] **MEDIA-02**: Photos are imported once into the media library with Arabic alt text and WebP sub-sizes; re-running the import creates no duplicates
- [ ] **MEDIA-03**: Each service and sub-item is assigned its matching PDF photos; galleries are grouped by the three service lines
- [ ] **MEDIA-04**: Layouts never upscale the ≤1280 px source photos into soft full-bleed images (split/inset hero compositions)

### Leads & Conversion (LEAD)

- [ ] **LEAD-01**: Visitor opens a floating WhatsApp button and chooses between the two numbers (with labels); the chat opens with a pre-filled message naming the current page/service
- [ ] **LEAD-02**: On mobile (≤767 px) visitor sees a sticky action bar (WhatsApp / call / quote) that respects safe areas and hides while the form is focused
- [ ] **LEAD-03**: Visitor submits a quote / site-visit request with: name, Saudi mobile, facility name, facility type, city/region, service (from service records), request type (quote / site visit / maintenance contract / spare parts / consultation), preferred contact method, optional email, details
- [ ] **LEAD-04**: Visitor sees Arabic inline validation (same rules in browser and server), a PDPL privacy notice and an unticked required consent checkbox; consent time is stored
- [ ] **LEAD-05**: Form works without JavaScript and is protected by nonce (cache-safe), honeypot, time trap and per-IP throttle (salted hash)
- [ ] **LEAD-06**: Buttons like «عقد صيانة» / «قطع غيار» open the form with service and request type preselected (URL presets)
- [ ] **LEAD-07**: After submitting, visitor sees a success state with a WhatsApp shortcut
- [ ] **LEAD-08**: Every request is stored in a private dashboard inbox even if email fails; admin gets an email notification (From domain, Reply-To visitor) and failures are flagged
- [ ] **LEAD-09**: Admin manages requests by status (new / contacted / closed) with filters, unread count, and one-click WhatsApp / call reply buttons
- [ ] **LEAD-10**: Admin exports requests to CSV
- [ ] **LEAD-11**: Each request records its source page, referrer, UTM parameters and device type
- [ ] **LEAD-12**: Personal data in requests can be exported/erased through WordPress privacy tools and old requests are purged after a configurable retention period

### Licensing & Trust (TRUST)

- [ ] **TRUST-01**: NCEC licensing appears only for the two licensed fields (remediation & rehabilitation plans; pollution-control filters) — on the home strip, licenses page, matching service pages and footer — and never on ventilation
- [ ] **TRUST-02**: Each license shows a «تحقق من الترخيص» link to the NCEC verification portal when a verification URL/number is filled
- [ ] **TRUST-03**: A «مرخّص» badge on a service page is derived only from a linked license record
- [ ] **TRUST-04**: Admin sees a dashboard notice when a license is within a configurable number of days of expiry
- [ ] **TRUST-05**: All copy is grounded in provided facts (no invented numbers, years, clients, projects, coordinates); a content-sources ledger records where each claim comes from

### Elementor Editing (EDIT)

- [ ] **EDIT-01**: Each page section is a custom «MWM» Elementor widget whose every text, image, link, toggle and repeater item is editable, with fields prefilled from live data
- [ ] **EDIT-02**: Every MWM widget has style controls (typography, colors, background, border/radius, spacing, responsive columns/gaps, alignment) from one shared framework
- [ ] **EDIT-03**: Untouched dynamic fields keep following dashboard data after saving (no frozen defaults); clearing a field hides that element
- [ ] **EDIT-04**: Buttons choose a link source (custom URL / page role / WhatsApp 1–2 / call 1–2 / email) so editors never paste phone numbers into links
- [ ] **EDIT-05**: Widgets render and re-initialize correctly inside the Elementor editor preview (sliders, reveal, gallery, form) with helpful empty states
- [ ] **EDIT-06**: Widget names and control IDs are frozen and documented before content seeding
- [ ] **EDIT-07**: Shortcodes `[mwm_phone]`, `[mwm_wa]`, `[mwm_email]` let editors use settings values inside Elementor's own widgets

### Sections (SECT)

- [ ] **SECT-01**: Header — logo, WP menu, phone, WhatsApp, «اطلب عرض سعر»; transparent on hero pages → solid on scroll; accessible mobile drawer
- [ ] **SECT-02**: Footer — logo, short about, menus, contacts, branches, license numbers, CR/VAT, socials (each hidden when empty), leaf-pattern brand band
- [ ] **SECT-03**: Home hero — static high-priority image by default (LCP-safe), title, slogan «خبراء جودة الهواء», CTAs; optional slider mode with visible pause control
- [ ] **SECT-04**: Inner page hero with breadcrumbs
- [ ] **SECT-05**: Services overview (3 service lines) as grid/slider with links to detail pages
- [ ] **SECT-06**: Sub-service items list per service (from records, with photos)
- [ ] **SECT-07**: Industries served (repeater: crushers, block plants, ready-mix, factories, mining, roads/spills, commercial & service buildings)
- [ ] **SECT-08**: Process steps (repeater: site visit → study/design → execution → maintenance)
- [ ] **SECT-09**: Why MWM (repeater of grounded capability facts: NCEC license, own filter workshops, trained installation/maintenance team, specialist engineers, three branches, Kingdom-wide coverage)
- [ ] **SECT-10**: Gallery with service-line tabs, accessible `<dialog>` lightbox, lazy-loaded tiles
- [ ] **SECT-11**: Licenses section (cards with issuer, field, number, verify link, linked services)
- [ ] **SECT-12**: Branches section (Jazan, Riyadh, Dammam + Kingdom-wide coverage; click-to-load map only when a location is filled)
- [ ] **SECT-13**: FAQ accordion
- [ ] **SECT-14**: CTA band (WhatsApp / call / quote) and contact info block
- [ ] **SECT-15**: Quote form widget (front end of LEAD-03..07)
- [ ] **SECT-16**: Company profile PDF download button (shown only when a file is set)

### Pages & Seeding (PAGE)

- [ ] **PAGE-01**: Pages exist and are built with MWM widgets: الرئيسية، من نحن، خدماتنا، 3 service detail pages، معرض الأعمال، التراخيص والاعتمادات، الفروع، طلب عرض سعر / معاينة، تواصل معنا، سياسة الخصوصية، 404
- [ ] **PAGE-02**: All service content from the client brief and the services PDF (3 lines, ~18 sub-items, licensing statements, spare parts, maintenance contracts) appears on the site in polished Arabic
- [ ] **PAGE-03**: Header/footer templates, menus, Kit globals and page roles are created automatically by an idempotent seeder (`wp mwm seed`) that never overwrites client edits
- [ ] **PAGE-04**: URLs use short Latin slugs (e.g. `/services/rehabilitation/`, `/quote/`)
- [ ] **PAGE-05**: A client confirmation checklist (entity type شركة/مؤسسة, Solenoid spelling, photo ownership & originals, license data, branch addresses, number labels, FAQ answers, CR/VAT/socials) is tracked and applied before seeding closes

### SEO (SEO)

- [ ] **SEO-01**: Each page has one H1, semantic headings, a unique title and meta description
- [ ] **SEO-02**: Pages output Open Graph / Twitter tags with a 1200×630 default share image
- [ ] **SEO-03**: JSON-LD graph: Organization (areaServed KSA + branch cities), Service per service line, BreadcrumbList; LocalBusiness per branch only once it has a real street address; backs off if Yoast/Rank Math is active
- [ ] **SEO-04**: Core XML sitemap excludes users, internal CPTs and unused post types; author archives disabled
- [ ] **SEO-05**: Optional GA4/GTM ID in settings loads analytics only when filled; contact clicks push `dataLayer` events

### Performance (PERF)

- [ ] **PERF-01**: Lighthouse mobile performance ≥ 90 on home and a service page (logged out, median of 3 runs)
- [ ] **PERF-02**: Hero image is a real `<img>` with eager loading and high fetch priority, never hidden by animation; other images lazy-load with srcset/sizes
- [ ] **PERF-03**: Only the CSS/JS of widgets used on a page loads; theme JS is deferred, dependency-free; block-library/global-styles CSS removed
- [ ] **PERF-04**: No layout shift from fonts, sliders or images (CLS < 0.1)

### Accessibility & Responsive (A11Y)

- [ ] **A11Y-01**: All text/UI color pairs meet WCAG 2.2 AA (leaf green / sky blue used only as decoration or on deep green); a safe-pairs table is documented for the client
- [ ] **A11Y-02**: Full keyboard navigation, visible focus, skip link, focus never obscured by sticky elements, touch targets ≥ 24 px
- [ ] **A11Y-03**: Any auto-moving content has pause control; `prefers-reduced-motion` disables non-essential motion
- [ ] **A11Y-04**: Layout verified without horizontal overflow at 1920 / 1440 / 1366 / 1280 / 1024 / 768 / 430 / 390 / 375 / 360

### Design & Motion (DSGN)

- [ ] **DSGN-01**: Light, clean "clean air" visual language (white/off-white surfaces, deep-green sections, leaf-pattern band, sky-blue air accents) consistent with the visual identity and logo clear-space rules
- [ ] **DSGN-02**: Premium, subtle motion (reveal/stagger, hover transforms) using transform/opacity only; first viewport content is never animated in

### Handoff (HAND)

- [ ] **HAND-01**: Arabic dashboard editing guide (how to edit each page/widget, settings, records, safe color pairs)
- [ ] **HAND-02**: Deploy runbook (search-replace incl. JSON-escaped URLs, Regenerate CSS, SMTP + SPF/DKIM test, cache exclusions, utf8mb4 dump, file-case check)

## v2 Requirements

### Content (deferred until real, permissioned data exists)

- **V2-01**: Projects / case studies with client names
- **V2-02**: Testimonials and client logos
- **V2-03**: Verified statistics / counters
- **V2-04**: English version of the site
- **V2-05**: Blog / news

### Features

- **V2-06**: Interactive KSA coverage map (SVG) with branches
- **V2-07**: Dedicated spare-parts catalogue page
- **V2-08**: Submitter auto-reply email
- **V2-09**: Analytics consent banner (if analytics scope grows)

## Out of Scope

| Feature | Reason |
|---------|--------|
| Elementor Pro / ACF Pro features | Client has Free versions only |
| Invented counters, years, clients, projects, coordinates | Content-integrity rule; damages trust and may mislead |
| NCEC / Vision 2030 / SGI logos as decoration | Government marks have usage restrictions; use text + client's own certificate |
| City × service landing pages | Doorway-page SEO risk with thin content |
| Separate public page per sub-service (~18) | Thin content; sub-items live inside service pages |
| File upload in the quote form | Security/PDPL burden; photos go via WhatsApp |
| Live-chat plugins, reCAPTCHA by default | Performance/privacy cost; honeypot+time trap+throttle suffice |
| Autoplay video / heavy hero slider by default | LCP and WCAG 2.2.2 cost |
| Popups, site search page | Low value for a small B2B site |
| E-commerce / online payment | Lead-generation site only |
| 29LT Bukra font | No web license; free alternative used |

## Traceability

Which phases cover which requirements. Updated during roadmap creation.

| Requirement | Phase | Status |
|-------------|-------|--------|
| FND-01 | Phase 1 | Pending |
| FND-02 | Phase 1 | Pending |
| FND-03 | Phase 1 | Pending |
| FND-04 | Phase 1 | Pending |
| FND-05 | Phase 1 | Pending |
| FND-06 | Phase 1 | Pending |
| FND-07 | Phase 1 | Pending |
| FND-08 | Phase 1 | Pending |
| SET-01 | Phase 2 | Pending |
| SET-02 | Phase 3 | Pending |
| SET-03 | Phase 2 | Pending |
| SET-04 | Phase 4 | Pending |
| SET-05 | Phase 2 | Pending |
| SET-06 | Phase 3 | Pending |
| DATA-01 | Phase 2 | Pending |
| DATA-02 | Phase 2 | Pending |
| DATA-03 | Phase 2 | Pending |
| DATA-04 | Phase 2 | Pending |
| DATA-05 | Phase 2 | Pending |
| DATA-06 | Phase 2 | Pending |
| MEDIA-01 | Phase 2 | Pending |
| MEDIA-02 | Phase 6 | Pending |
| MEDIA-03 | Phase 6 | Pending |
| MEDIA-04 | Phase 5 | Pending |
| LEAD-01 | Phase 3 | Pending |
| LEAD-02 | Phase 3 | Pending |
| LEAD-03 | Phase 3 | Pending |
| LEAD-04 | Phase 3 | Pending |
| LEAD-05 | Phase 3 | Pending |
| LEAD-06 | Phase 3 | Pending |
| LEAD-07 | Phase 3 | Pending |
| LEAD-08 | Phase 3 | Pending |
| LEAD-09 | Phase 3 | Pending |
| LEAD-10 | Phase 3 | Pending |
| LEAD-11 | Phase 3 | Pending |
| LEAD-12 | Phase 3 | Pending |
| TRUST-01 | Phase 6 | Pending |
| TRUST-02 | Phase 5 | Pending |
| TRUST-03 | Phase 5 | Pending |
| TRUST-04 | Phase 2 | Pending |
| TRUST-05 | Phase 6 | Pending |
| EDIT-01 | Phase 5 | Pending |
| EDIT-02 | Phase 4 | Pending |
| EDIT-03 | Phase 4 | Pending |
| EDIT-04 | Phase 4 | Pending |
| EDIT-05 | Phase 4 | Pending |
| EDIT-06 | Phase 5 | Pending |
| EDIT-07 | Phase 4 | Pending |
| SECT-01 | Phase 5 | Pending |
| SECT-02 | Phase 5 | Pending |
| SECT-03 | Phase 5 | Pending |
| SECT-04 | Phase 4 | Pending |
| SECT-05 | Phase 4 | Pending |
| SECT-06 | Phase 5 | Pending |
| SECT-07 | Phase 5 | Pending |
| SECT-08 | Phase 5 | Pending |
| SECT-09 | Phase 5 | Pending |
| SECT-10 | Phase 5 | Pending |
| SECT-11 | Phase 5 | Pending |
| SECT-12 | Phase 5 | Pending |
| SECT-13 | Phase 5 | Pending |
| SECT-14 | Phase 5 | Pending |
| SECT-15 | Phase 5 | Pending |
| SECT-16 | Phase 5 | Pending |
| PAGE-01 | Phase 6 | Pending |
| PAGE-02 | Phase 6 | Pending |
| PAGE-03 | Phase 6 | Pending |
| PAGE-04 | Phase 6 | Pending |
| PAGE-05 | Phase 6 | Pending |
| SEO-01 | Phase 7 | Pending |
| SEO-02 | Phase 7 | Pending |
| SEO-03 | Phase 7 | Pending |
| SEO-04 | Phase 7 | Pending |
| SEO-05 | Phase 7 | Pending |
| PERF-01 | Phase 7 | Pending |
| PERF-02 | Phase 7 | Pending |
| PERF-03 | Phase 7 | Pending |
| PERF-04 | Phase 7 | Pending |
| A11Y-01 | Phase 7 | Pending |
| A11Y-02 | Phase 7 | Pending |
| A11Y-03 | Phase 5 | Pending |
| A11Y-04 | Phase 7 | Pending |
| DSGN-01 | Phase 5 | Pending |
| DSGN-02 | Phase 5 | Pending |
| HAND-01 | Phase 7 | Pending |
| HAND-02 | Phase 7 | Pending |

**Coverage:**
- v1 requirements: 86 total
- Mapped to phases: 86
- Unmapped: 0 ✓

---
*Requirements defined: 2026-10-04*
*Last updated: 2026-10-04 after roadmap creation (traceability mapped)*
