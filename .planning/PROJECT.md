# MWM — Environmental Services Website (mwmpower-ksa)

## What This Is

A custom, fully responsive, Arabic-first (RTL) WordPress theme and corporate website for **MWM للمعالجة البيئية والحلول المستدامة** (MWM for Environmental Remediation & Sustainable Solutions) — a Saudi environmental services establishment with branches in Jazan, Riyadh and Dammam, serving the whole Kingdom. The site presents its three licensed service lines (remediation & rehabilitation plans, pollution-control filters, ventilation systems), proves credibility (NCEC licensing, real field photos), and converts B2B visitors (crushers, block/ready-mix plants, factories, industrial/commercial facilities) into WhatsApp chats, calls and quote/site-visit requests. Every visible text, image, color and spacing is editable by the client through **Elementor (Free)** and **ACF (Free)** — nothing marketing-related is hardcoded.

## Core Value

A facility owner in Saudi Arabia lands on the site from any device, immediately understands that MWM is an NCEC-licensed air-quality and remediation expert, and can reach them (WhatsApp / call / quote request) in one tap — while the client can change any content themselves from Elementor without a developer.

## Business Context

- **Customer**: Saudi industrial and commercial facility owners/operators — crushers & quarries, concrete block and ready-mix plants, factories, mining sites, logistics/road authorities (spills), commercial and service buildings needing ventilation/odor control.
- **Revenue model**: B2B service contracts (rehabilitation plan execution, filter supply/installation/maintenance contracts, spare parts, ventilation design & build).
- **Success metric**: Qualified leads — quote/site-visit requests in the dashboard inbox + WhatsApp chats + calls.
- **Strategy notes**: Slogan «خبراء جودة الهواء». Quality bar = the previously delivered Al-Masa theme (`C:\xampp\htdocs\al-masa`), but better on every axis (see Key Decisions).

## Requirements

### Validated

- ✓ WordPress core installed in `C:\xampp\htdocs\mwmpower-ksa` (XAMPP, Windows) — existing
- ✓ `wp-config.php` connected to MySQL database `mwmpower-ksa` (root / no password, prefix `wp_`, `WP_DEBUG` + log on, display off) — initialization
- ✓ WordPress installed by the client on the DB: site title `MWM`, locale `ar`, permalinks `/%postname%/`, admin email `mwmksa92@gmail.com`, 1 admin user; active theme still `twentytwentyfive`, no plugins yet — existing
- ✓ Media already uploaded by the client (2026/10): logo PNG (attachment 6, `WhatsApp-Image-2026-10-03-at-1.53.52-PM-1.png`), logo on white canvas (7 jpeg / 8 png), Site Icon set (attachment 9, cropped), services PDF (10, `خدمات-المؤسسة.pdf`), visual identity PDF (11, `mwm-logo-visual-identity.pdf`) — identical (MD5) to `docs/source/*.pdf` — existing

### Active

- [ ] Custom theme `mwm` (prefix `mwm_`, text domain `mwm`), RTL-first, translation-ready for future English
- [ ] Elementor Free + ACF Free installed and activated; theme `mwm` activated; existing logo attachment reused as Site Logo (no duplicate uploads)
- [ ] Design tokens from the visual identity mapped to CSS variables AND Elementor Kit (global colors + typography)
- [ ] Custom Elementor widget per section (category «MWM») — every text, image, link, color, typography, spacing and visibility controllable from Elementor
- [ ] Global site settings in one place (phones, WhatsApp numbers, email, social links, CR/VAT, license numbers, branch data) feeding header, footer, floating buttons, schema
- [ ] Structured data via CPTs + ACF Free: services (3 main + sub-services), gallery/projects, branches, licenses, quote requests inbox
- [ ] Pages: الرئيسية، من نحن، خدماتنا + صفحة لكل خدمة، معرض الأعمال، التراخيص والاعتمادات، الفروع، طلب عرض سعر / معاينة، تواصل معنا
- [ ] Conversion: floating WhatsApp (both numbers, visitor picks), `tel:` call buttons, quote/site-visit form with dashboard inbox + email notification
- [ ] All service content and photos from the services PDF used on the site
- [ ] Light, clean "clean air" visual direction with deep-green sections; premium motion that respects reduced motion
- [ ] Performance (Lighthouse 90+ mobile), local SEO (LocalBusiness schema per branch, Open Graph, sitemap), WCAG AA accessibility
- [ ] Responsive QA across 1920 → 360 widths, Elementor editability QA

### Out of Scope

- English content in v1 — Arabic only now; theme strings stay translatable so English can be added later
- Elementor Pro / ACF Pro features (Theme Builder, Repeater, Gallery, Options page, Pro forms) — client has Free versions only; repeatable data = CPTs, settings = Customizer/custom admin page
- Blog / news section — not selected for v1
- Invented statistics, years of experience, client names, project names, coordinates or license numbers — content must be grounded in provided facts; unknown data ships as empty editable fields that hide when empty
- E-commerce / online payments — B2B lead generation only
- The commercial font 29LT Bukra — client has no web license; a free Arabic font with a similar geometric character is used instead

## Context

### Company facts (source of truth — do not expand with invented claims)

- **Official name (site):** MWM للمعالجة البيئية والحلول المستدامة — EN: MWM for Environmental Remediation & Sustainable Solutions
- **Type:** Saudi environmental services establishment (the services PDF refers to it as «المؤسسة»)
- **Branches:** منطقة جازان، الرياض، الدمام — operates across the whole Kingdom. Detailed addresses not provided yet → editable fields, maps only when an editor fills a location.
- **Slogan:** خبراء جودة الهواء
- **Licensing:** Licensed by **المركز الوطني للرقابة على الالتزام البيئي** (NCEC) in two fields: (1) تنفيذ خطط العلاج والتأهيل، (2) فلاتر كبح الملوثات. License numbers/images not provided yet → editable fields.
- **Email:** mwmksa92@gmail.com
- **Phones:** 0573825004 — 0573832990 (both also WhatsApp; Saudi format → `+966573825004`, `+966573832990`)
- **Social / CR / VAT:** not provided → editable, hidden until filled

### About copy (provided by client)

شركة خدمات بيئية سعودية لها فروع في منطقة جازان والرياض والدمام وتعمل في جميع أنحاء المملكة.

### Service catalogue (provided text + services PDF — use all of it)

**أولاً: تنفيذ خطط العلاج والتأهيل**
- الشركة مرخصة من المركز الوطني للرقابة على الالتزام البيئي في مجال تنفيذ خطط العلاج والتأهيل.
- تنفيذ خطط إعادة التأهيل البيئي للمواقع المتدهورة، وخطط إعادة التأهيل الموافق عليها من المركز.
- إعادة تأهيل الكسارات والمحاجر والمواقع التعدينية.
- خطط إعادة التأهيل بغرض إغلاق الأنشطة ذات التأثيرات البيئية مثل الكسارات والمصانع وغيرها.
- تنفيذ خطط معالجة المواقع الملوثة بانسكابات المواد البترولية والمواد الكيميائية.
- معالجة الانسكابات الناتجة عن حوادث الشاحنات الناقلة للمواد الخطرة على الطرق السريعة والداخلية.

**ثانياً: توريد وتركيب وصيانة فلاتر كبح الملوثات**
- مرخصة من المركز الوطني للرقابة على الالتزام البيئي في مجال فلاتر كبح الملوثات.
- ورش لتصنيع فلاتر كبح الملوثات مثل فلاتر الغبار للكسارات ومصانع طوب البلوك والخرسانة الجاهزة.
- تصميم وتركيب فلاتر كبح الملوثات للكسارات والمصانع المختلفة.
- عقود الصيانة لفلاتر كبح الملوثات للكسارات ومصانع الخرسانة الجاهزة وغيرها من المصانع.
- فريق مدرب للتركيب والصيانة الدورية.
- توفير قطع غيار Bag Filters لجميع الخامات (القماشية والفايبرجلاس).
- توفير قطع غيار لكل أنواع الـ Valves (PDF spells "Sonolite Valve" — almost certainly *Solenoid Valve*; confirm with client) من ماركات معروفة مع توفير الضمان.

**ثالثاً: تصميم وتنفيذ أنظمة التهوية**
- فريق من المهندسين المختصين في تصميم أنظمة التهوية للمنشآت الصناعية والتجارية والخدمية، وفنيين خبراء، وتنفيذ التصميمات بجودة عالية.
- تصميم وتوريد وتركيب أنظمة مجاري الهواء (Duct system).
- توريد وتركيب مراوح الشفط العمودية والأفقية بقدرات مختلفة.
- تصميم وتوريد وتركيب فلاتر مكافحة الروائح: فلاتر الكربون النشط، الفلاتر الحيوية، الفلاتر الكيميائية.
- أنظمة كبح الغبار والروائح وغيرها من التطبيقات اللازمة لضمان جودة الهواء داخل بيئة العمل.

### Source assets (in repo `docs/source/`)

- `logo.png` — primary combination mark (icon: two green building/leaf shapes + "MWM" block)
- `visual-identity.pdf` (16 pages) — rendered to `docs/source/pages/identity/NN.png`: logo concept (leaf = sustainability, base = strong foundation, buildings = inspired by Dammam & Riyadh), primary/secondary versions, backgrounds, do/don't usage, clear space, colors, typography, mockups (signage, stationery)
- `services.pdf` (17 pages, ~25 real photos) — rendered to `docs/source/pages/services/NN.png`. Page → service map: 1–4 rehabilitation of crushers/quarries/mining (loaders, dozers, excavators); 5–8 contaminated sites & hazardous-spill remediation; 9–10 filter design/installation/maintenance; 11 bag-filter spares; 12 valve spares; 13 ventilation intro; 14 duct systems; 15 exhaust fans; 16–17 odor-control filters (scrubbers, carbon/bio units). Photos must be extracted at full resolution into the media library (not page screenshots).
- PDF footer motif: a band of repeating light-green leaf shapes — reusable brand pattern.

### Brand tokens (from visual identity)

| Role | Hex |
| --- | --- |
| Primary light green (leaf) | `#5BBF75` |
| Primary green | `#28864B` |
| Primary deep green | `#194223` |
| Secondary off-white | `#EAEAEA` |
| Secondary sky blue (air) | `#A5D7FC` |
| Secondary royal blue | `#1A2BC3` |
| Secondary charcoal green | `#3A423C` |

- Identity font: 29LT Bukra (Variable) — replaced on the web by a free Arabic font of similar geometric/wide character (picked during research; candidates: Readex Pro, Alexandria, IBM Plex Sans Arabic, Tajawal).
- Logo rules: respect clear space, approved backgrounds, no distortion/recoloring (see identity pages 6–11).

### Reference implementation (Al-Masa)

`C:\xampp\htdocs\al-masa` — custom theme `almasa` built the same way (Elementor Free + ACF Free). Reuse its proven architecture, improved:
- `PROJECT_RULES.md` at root as canonical rules; `.cursor/rules` + project skills (content-data, elementor-widget, verify)
- `inc/` modules: setup, enqueue, post-types, acf (local field groups + `acf-json/`), elementor (locations, header/footer via Customizer template pick), `elementor/` widgets (base class `Almasa_Widget` rendering `template-parts/sections/*` via `get_template_part` — one markup source for widget + PHP fallback), helpers (`almasa_arg()` absent=default / empty=hidden semantics), contact-form (CPT inbox, nonce, honeypot, throttle, `wp_mail`, no-JS fallback), social (Customizer), media, seed-once (idempotent content seeding), security
- `assets/css/tokens.css`, `base.css`, `site.css`, `motion.css`; `assets/js/theme.js` idempotent `init(scope)` re-run on Elementor `element_ready`
- Sliders = native CSS scroll-snap + controls; gallery tabs/filters + lightbox; transparent header on hero pages
- Git: only theme + docs versioned

### Environment

- Windows, XAMPP (Apache + MySQL/MariaDB + PHP at `C:\xampp\php\php.exe`), local URL `http://localhost/mwmpower-ksa`
- Git at `C:\Program Files\Git\cmd` (not on PATH by default); Node 24 available (no Python)
- Theme author (theme headers only, never on the public site): MohamedAmjad — mohamed-amjad.com

## Constraints

- **Tech stack**: WordPress + custom classic PHP theme `mwm` + Elementor **Free** + ACF **Free** — client only has free versions; no Pro-only features (no Theme Builder, Repeater, Gallery, Options page)
- **Editability**: No visible marketing copy, logos, image URLs, nav labels, phones, addresses in PHP/JS/CSS — everything editable via Elementor widgets, WP menus, media library, CPT/ACF, or the global settings screen
- **Direction**: RTL-first Arabic (`dir="rtl"` from WP locale) — not a flipped LTR layout; strings translatable for future English
- **Content integrity**: No invented numbers, years, clients, projects, license numbers or coordinates; empty fields hide their UI
- **Brand**: Colors, logo usage and clear-space rules from the visual identity PDF
- **Performance**: Lighthouse mobile 90+, minimal JS, no unused libraries, lazy images, modern formats, fonts self-hosted with `font-display: swap`
- **Accessibility**: WCAG 2.2 AA — contrast (note: `#5BBF75` on white fails for text; use it for shapes/accents only), keyboard, visible focus, semantic headings (one H1), reduced motion
- **Responsive**: First-class layouts at 1920/1440/1366/1280, 1024/768, 430/390/375/360

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| Follow Al-Masa architecture (custom theme + one Elementor widget per section + CPT/ACF data) | Proven on a delivered project with the same free plugin stack | — Pending |
| "Better than Al-Masa" = deeper Elementor control (content + style controls on every widget), stronger design & motion, performance 90+, local SEO schema, WCAG AA, one global settings screen for contacts | Explicit client priorities | — Pending |
| Arabic-only v1, translation-ready | Client choice; English later | — Pending |
| Light/clean "clean air" visual direction with deep-green sections | Client choice; matches "خبراء جودة الهواء" | — Pending |
| Free Arabic font replacing 29LT Bukra | No web license for the commercial font | — Pending |
| WhatsApp: both numbers, visitor chooses; WhatsApp + call + quote form equal priority | Client choice | — Pending |
| Grounded professional copywriting (no invented claims) | Client choice | — Pending |
| Horizontal layers build order (foundation → data → widgets → pages → QA) | Client choice; mirrors Al-Masa delivery | — Pending |
| Gallery organized by the three service lines from PDF photos (no named projects yet) | No project names/clients provided | — Pending |
| Theme slug `mwm`, prefix `mwm_`, text domain `mwm` | Short, brand-aligned | — Pending |

## Evolution

This document evolves at phase transitions and milestone boundaries.

**After each phase transition** (via `/gsd-transition`):
1. Requirements invalidated? → Move to Out of Scope with reason
2. Requirements validated? → Move to Validated with phase reference
3. New requirements emerged? → Add to Active
4. Decisions to log? → Add to Key Decisions
5. "What This Is" still accurate? → Update if drifted

**After each milestone** (via `/gsd-complete-milestone`):
1. Full review of all sections
2. Core Value check — still the right priority?
3. Audit Out of Scope — reasons still valid?
4. Update Context with current state

---
*Last updated: 2026-10-04 after initialization*
