# Architecture Research

**Domain:** Custom classic WordPress theme `mwm` (prefix `mwm_`, text domain `mwm`) — Arabic RTL B2B corporate/lead-gen site. Elementor **Free** owns layout/copy/imagery through custom per-section widgets; ACF **Free** + CPTs own structured data. No Theme Builder, no ACF Repeater/Gallery/Options page.
**Researched:** 2026-10-04
**Confidence:** HIGH for the baseline (read directly from the delivered Al-Masa theme code) · MEDIUM-HIGH for Elementor/ACF API claims (official developer docs + Elementor source/issue tracker) · items marked **[VERIFY]** must be proven with a spike in the phase that uses them.

---

## 0. TL;DR — what to adopt, what to change

Adopt Al-Masa's skeleton almost 1:1 (it is proven): modular `inc/`, one Elementor widget per section extending a base class that renders `template-parts/sections/*` via `get_template_part()` (one markup source for widget + PHP fallback), `mwm_arg()` "absent = default / empty = hidden" semantics, CPT inbox form with nonce/honeypot/throttle/no-JS fallback, Customizer-free header/footer via saved Elementor templates, idempotent `init(scope)` JS re-run on Elementor `element_ready`.

Change these ten things (each is a concrete weakness found in the Al-Masa code):

| # | Al-Masa today (evidence) | MWM architecture |
|---|---|---|
| 1 | `style_controls()` gives only bg color, top/bottom padding, and color+typography for 3 text parts; header/footer/marquee hand-roll their own style sections | Declarative **style parts** (`box`, `text`, `button`, `card`, `media`, `grid`, `icon`) implemented as reusable **traits**; every widget declares a selector map and gets full responsive style controls for free |
| 2 | Every widget hand-writes `template_args()` with repetitive `show_x`/empty/absent branching (~30 lines each) | Declarative **content field specs**; base class auto-registers controls and auto-maps settings → args with the semantics built in; `template_args()` only for exceptions (repeaters) |
| 3 | Dynamic values put into control `default` (page title, site name). Elementor's save path keeps **TEXT/TEXTAREA values even when equal to default** (`toJSON({removeDefault})` skips text/textarea with a value) → the "follows the dashboard" promise silently becomes a snapshot once the page is saved | Dynamic text = **empty default + `placeholder` showing the live value**; empty → template falls back to live data. Media: empty default, preview still shows the live image because the template falls back |
| 4 | Pages/links resolved **by Arabic title** (`almasa_page_url('تواصل معنا')`, `page.php` `$sections_by_title`, ACF About group located by title lookup on every `acf/init`) | **Page-role registry** (`mwm_page_id('quote')`) stored in settings, set by the seeder, editable via dropdowns; renaming a page never breaks links |
| 5 | Contacts split across CPT (`almasa_contact`), Customizer (social), `admin_email` (notifications); phone normalizer hard-codes Egypt (`tel:+2…`) | One **schema-driven Settings API page** (`mwm_settings` option) feeding header, footer, floating WhatsApp, CTA buttons, form notifications, JSON-LD; Saudi E.164 helpers (`+966`) |
| 6 | `tokens.css` hard-codes hex values; Kit colors set separately → two sources that drift | Tokens **bridge to Kit variables**: `--mwm-green: var(--e-global-color-mwm_green, #28864B)`; seeder writes Kit globals with **fixed `_id`s** |
| 7 | Google Fonts CDN; one 51 KB `site.css` on every page | Self-hosted fonts registered into Elementor's font list; core CSS + per-widget CSS enqueued in `<head>` from a pre-scan of the page's Elementor data |
| 8 | Seeder guarded only by one global flag; projects inserted without existence checks; pages created **empty** (Elementor layouts built by hand); deprecated `get_page_by_title()` | **Versioned, step-based, per-entity idempotent seeder** keyed by `_mwm_seed_key`, with a "don't overwrite client edits" hash guard, deterministic Elementor JSON builder, Kit + menus + roles + media manifest |
| 9 | `is_dynamic_content()` returns `true` for every widget (no element caching) | `true` only for data-fed widgets; static copy widgets return `false` (Elementor element caching) |
| 10 | Page-local marketing lists modeled as CPTs (`almasa_reason` for "why us") | Rule: **reused / queried / schema-relevant → CPT; page-local presentational list → Elementor widget REPEATER** (repeater controls *are* available in Elementor Free) |

---

## 1. Standard Architecture

### 1.1 System Overview

```
┌────────────────────────────────────────────────────────────────────────────┐
│                         AUTHORING (client-facing)                           │
│  ┌──────────────────┐ ┌────────────────────┐ ┌───────────────┐ ┌─────────┐ │
│  │ Elementor editor │ │ WP admin: CPT + ACF│ │ MWM Settings  │ │ Menus / │ │
│  │ pages, service   │ │ services, items,   │ │ page (Settings│ │ Media / │ │
│  │ singles, header/ │ │ galleries, branches│ │ API) contacts,│ │ Site    │ │
│  │ footer templates,│ │ licenses, requests │ │ socials, CR/  │ │ Logo    │ │
│  │ Kit globals      │ │ inbox (read-only)  │ │ VAT, roles,   │ │         │ │
│  └────────┬─────────┘ └─────────┬──────────┘ │ header/footer │ └────┬────┘ │
│           │                     │            └───────┬───────┘      │      │
├───────────┴─────────────────────┴────────────────────┴──────────────┴──────┤
│                          STORAGE (WordPress DB / uploads)                   │
│  _elementor_data / Kit meta │ CPT posts + postmeta │ wp_options:           │
│  elementor_library templates│ (ACF field = meta key)│ mwm_settings,         │
│                             │ attachments + alt     │ mwm_seed_state        │
├─────────────────────────────────────────────────────────────────────────────┤
│                    DOMAIN / DATA ACCESS  (inc/data/*.php)                   │
│  mwm_setting() · mwm_page_id()/mwm_page_url() · mwm_services()              │
│  mwm_service_items() · mwm_gallery_ids() · mwm_branches() · mwm_licenses()  │
│  mwm_phone_e164()/mwm_tel_href()/mwm_wa_url() · mwm_context_post_id()       │
│  (pure read functions, static-cached per request, ACF-optional)             │
├─────────────────────────────────────────────────────────────────────────────┤
│                         RENDERING                                           │
│  ┌──────────────────────────────┐      ┌─────────────────────────────────┐  │
│  │ Elementor layer (inc/elementor)│ ──▶ │ template-parts/sections/*.php   │  │
│  │ Mwm_Widget base + traits      │ args │ (ONE markup source; reads args, │  │
│  │ content specs → args          │      │  falls back to domain helpers)  │  │
│  │ style parts → {{WRAPPER}} CSS │      └───────────────▲─────────────────┘  │
│  └──────────────────────────────┘                      │ args             │
│  ┌──────────────────────────────┐                      │                  │
│  │ PHP fallback templates        │ ─────────────────────┘                  │
│  │ front-page/page/single-mwm_   │  (section map by page ROLE, not title)  │
│  │ service.php, header/footer    │                                          │
│  └──────────────────────────────┘                                          │
│  Global chrome (theme-owned): skip link, floating WhatsApp/call, JSON-LD,  │
│  OG meta — rendered from mwm_settings, never from Elementor                │
├─────────────────────────────────────────────────────────────────────────────┤
│                         CLIENT (browser)                                    │
│  tokens.css → base/core CSS → per-widget CSS · theme.js module registry     │
│  init(scope) on load + Elementor frontend/element_ready (editor preview)    │
└─────────────────────────────────────────────────────────────────────────────┘
        ▲ write paths:  form → admin-ajax/admin-post → mwm_request CPT + wp_mail
        ▲ seed path:    seed data + media manifest → seeder → all stores above
```

### 1.2 Component Responsibilities

| Component | Owns | Must NOT | Implementation |
|---|---|---|---|
| **Bootstrap** (`functions.php`) | Constants, ordered `require` of modules | Contain logic or hooks | Array of module paths, `require_once` loop |
| **Core helpers** (`inc/core/`) | `mwm_arg()`, `mwm_arg_url()`, `mwm_get_field()`, escaping helpers, phone utils, icon SVG sprite helper | Register hooks or query at include time | Pure functions |
| **Settings** (`inc/settings/`) | Schema of all global fields, admin page, sanitization, `mwm_setting()` getter, page-role registry, header/footer template choice | Store repeatable records (those are CPTs) | Settings API, one option `mwm_settings` (autoload) |
| **Content model** (`inc/content/`) | CPT + ACF registration, gallery metabox, admin columns/filters, data notes | Render front-end markup | `register_post_type`, `acf_add_local_field_group` on `acf/init` |
| **Domain/data access** (`inc/data/`) | Query functions returning arrays/IDs, static cache | Echo HTML | `WP_Query` with `no_found_rows`, `fields => ids` where possible |
| **Template parts** (`template-parts/`) | All front-end markup, a11y, escaping, `data-*` hooks for JS | Hard-code marketing copy; query Elementor | `get_template_part( $slug, null, $args )` |
| **Elementor layer** (`inc/elementor/`) | Category, base widget, traits, widget classes, document controls (header style), header/footer resolution, Kit/font integration, asset pre-scan | Own data (reads domain helpers only) | Hooks: `elementor/widgets/register`, `elementor/elements/categories_registered`, `elementor/documents/register_controls` |
| **Global chrome** (`inc/chrome/`) | Floating contact (WhatsApp picker + call), skip link, body classes | Depend on Elementor | `wp_footer` / `wp_body_open` |
| **Leads** (`inc/leads/`) | Quote/site-visit form processing, validation rules shared with JS, `mwm_request` inbox, notifications, privacy exporter/eraser | Expose requests publicly | admin-ajax + admin-post, `wp_mail` |
| **SEO** (`inc/seo/`) | JSON-LD graph (Organization, LocalBusiness per branch, Service, BreadcrumbList), OG fallback, sitemap filters | Duplicate an active SEO plugin | `wp_head`, `wp_sitemaps_*` filters; auto-off if Rank Math/Yoast active |
| **Assets** (`inc/assets.php` + `assets/`) | Register/enqueue core + per-widget CSS/JS, self-hosted fonts, localized config | Load libraries "just in case" | `wp_register_*`, widget `get_style_depends()` |
| **Seeder** (`inc/seed/`) | Idempotent creation of media, CPT entries, options, Kit, templates, Elementor pages, menus, roles | Run on normal requests | WP-CLI command `wp mwm seed` + admin Tools fallback; loaded only in CLI/admin |
| **Dev tools** (`tools/`, not in theme) | PDF photo extraction → `docs/source/photos/` + `manifest.json` | Run inside WordPress | Node 24 script |

---

## 2. Recommended Project Structure

```
wp-content/themes/mwm/
├── style.css                 # theme header only (author: MohamedAmjad)
├── functions.php             # constants + ordered module list (no logic)
├── theme.json                # minimal: disables block-editor color/typography noise
├── header.php / footer.php   # <html dir> shell → mwm_render_site_part()
├── index.php  404.php  page.php  front-page.php  single.php
├── single-mwm_service.php    # Elementor content if built, else PHP section map
├── PROJECT_RULES.md          # deploy copy (root copy is canonical)
├── inc/
│   ├── core/
│   │   ├── helpers.php       # mwm_arg(), mwm_arg_url(), mwm_get_field(), mwm_icon()
│   │   └── phone.php         # mwm_phone_e164(), mwm_tel_href(), mwm_wa_url(), display format
│   ├── setup.php             # supports, menus, image sizes, textdomain, content width
│   ├── assets.php            # fonts, core CSS, per-widget handles, theme.js + config
│   ├── settings/
│   │   ├── schema.php        # mwm_settings_schema(): single source of field definitions
│   │   ├── options.php       # mwm_setting(), mwm_page_id(), mwm_page_url(), filters
│   │   └── admin-page.php    # Settings API page, tabs, sanitize via schema
│   ├── content/
│   │   ├── post-types.php    # mwm_service, mwm_service_item, mwm_branch, mwm_license
│   │   ├── acf-fields.php    # local field groups (PHP = canonical)
│   │   ├── gallery-metabox.php # per-service ordered attachment IDs (ACF-Pro-compatible key)
│   │   └── admin-ux.php      # columns, filters by service, order, data notes
│   ├── data/
│   │   ├── queries.php       # mwm_services(), mwm_service_items(), mwm_branches(), mwm_licenses()
│   │   └── context.php       # mwm_context_post_id() (editor, preview, AJAX, front)
│   ├── elementor/
│   │   ├── bootstrap.php     # guard on did_action('elementor/loaded'); category; register widgets
│   │   ├── class-mwm-widget.php        # base: content specs, style parts, render → template part
│   │   ├── traits/
│   │   │   ├── trait-content-fields.php # text/textarea/media/link/toggle/select/repeater specs
│   │   │   ├── trait-style-box.php      # background group, padding/margin, min-height, border, shadow
│   │   │   ├── trait-style-text.php     # color, typography, align, spacing, text-shadow
│   │   │   ├── trait-style-button.php   # typography, padding, radius, normal/hover tabs
│   │   │   ├── trait-style-card.php     # bg, border, radius, shadow, padding, hover lift
│   │   │   ├── trait-style-media.php    # aspect-ratio, object-fit, radius, overlay
│   │   │   └── trait-style-grid.php     # responsive columns/gap via CSS custom props
│   │   ├── widgets/          # one file per widget: class-mwm-widget-hero.php …
│   │   ├── site-parts.php    # header/footer resolution + head CSS enqueue of chosen templates
│   │   ├── document-controls.php # page setting "Header style: auto/transparent/solid"
│   │   ├── kit.php           # fonts into Elementor font list; scheme/Google-font options
│   │   └── asset-scan.php    # pre-scan _elementor_data → enqueue widget CSS in <head>
│   ├── chrome/
│   │   ├── floating-contact.php # WhatsApp number picker + call button (settings-driven)
│   │   └── body-classes.php
│   ├── leads/
│   │   ├── rules.php         # mwm_form_rules(): fields, regex, Arabic messages (→ JS too)
│   │   ├── request-cpt.php   # mwm_request (non-public), columns, status meta, unread bubble
│   │   ├── handler.php       # nonce, honeypot, throttle, validate, insert, notify
│   │   └── privacy.php       # personal-data exporter/eraser for requests (PDPL-friendly)
│   ├── seo/
│   │   ├── schema-graph.php  # JSON-LD @graph
│   │   └── meta.php          # OG/Twitter fallback when no SEO plugin
│   ├── media.php             # attachment URL encoding, image sizes, alt fallbacks
│   ├── security.php          # small hardening only (no plugin-level features)
│   └── seed/                 # loaded ONLY under WP_CLI or the admin seed screen
│       ├── class-mwm-seeder.php # step runner, state, hash guard
│       ├── elementor-builder.php # mwm_el_container()/mwm_el_widget(), deterministic IDs
│       ├── data/             # content.php (copy), services.php, branches.php, pages.php, kit.php
│       └── cli.php           # WP_CLI::add_command('mwm seed', …)
├── template-parts/
│   ├── site/                 # header.php, footer.php, floating-contact.php, breadcrumbs.php
│   ├── sections/             # hero, page-hero, intro, services, service-items, sectors,
│   │                         # why, licenses, gallery, branches, cta-band, quote-form,
│   │                         # contact-info, stats(derived only), ticker
│   └── components/           # button.php, section-head.php, card-service.php, phone-link.php
├── assets/
│   ├── css/
│   │   ├── tokens.css        # --mwm-* bridged to --e-global-* with brand fallbacks
│   │   ├── base.css          # reset, typography, focus, RTL logical properties
│   │   ├── core.css          # shell, buttons, section head, header/footer, floating contact
│   │   ├── motion.css        # reveal/stagger + prefers-reduced-motion
│   │   └── widgets/          # hero.css, gallery.css, quote-form.css … (one per section)
│   ├── js/theme.js           # module registry, init(scope), Elementor hooks
│   ├── fonts/                # self-hosted woff2 (subset Arabic + Latin)
│   └── icons/sprite.svg      # brand/UI icons (no icon-font libraries)
├── acf-json/                 # save/load point only for groups a dev adds in UI (PHP is canonical)
└── languages/                # mwm.pot (all strings via __( '…', 'mwm' ))

<repo root>/tools/extract-pdf-images.mjs   # dev-only; outputs docs/source/photos/** + manifest.json
<repo root>/docs/source/photos/manifest.json # file → service/item, alt text (Arabic), PDF page
```

### Structure Rationale

- **`inc/` grouped by responsibility, not by WordPress hook** — the roadmap phases map to folders (settings → content → data → elementor → leads → seo → seed), so a phase touches one folder plus its template parts.
- **`data/` separate from `content/`** — registration (admin concern) and querying (render concern) change for different reasons; widgets and fallbacks depend only on `data/`, which makes them testable with seeded data alone.
- **Widgets one-per-file** (Al-Masa had a 775-line `widgets-sections.php`) — easier reviews, parallel work, and clean `get_style_depends()` per widget.
- **`template-parts/components/`** — buttons/section heads were duplicated inline across Al-Masa section templates; components keep one markup per atom so style-part selectors stay stable.
- **`seed/` isolated and conditionally loaded** — zero cost on front-end requests; seed copy lives in data files, not in widget defaults.
- **`tools/` outside the theme** — PDF extraction needs Node packages; the theme stays dependency-free.

### Bootstrapping Order (`functions.php`)

```php
define( 'MWM_VERSION', '1.0.0' );
define( 'MWM_DIR', get_template_directory() );
define( 'MWM_URI', get_template_directory_uri() );

$mwm_modules = array(
	'core/helpers', 'core/phone',               // 1. pure functions (no hooks)
	'settings/schema', 'settings/options',       // 2. global data getters (used by everything below)
	'setup', 'media', 'security',                // 3. theme supports, sizes
	'content/post-types', 'content/acf-fields',  // 4. content model (hooks: init / acf/init)
	'content/gallery-metabox', 'content/admin-ux',
	'data/context', 'data/queries',              // 5. read layer
	'assets',                                    // 6. needs settings (font choice) + data (form rules)
	'chrome/body-classes', 'chrome/floating-contact',
	'leads/rules', 'leads/request-cpt', 'leads/handler', 'leads/privacy',
	'seo/schema-graph', 'seo/meta',
	'elementor/bootstrap',                       // 7. only adds hooks; classes load on elementor/widgets/register
);
if ( is_admin() ) {
	$mwm_modules[] = 'settings/admin-page';
}
foreach ( $mwm_modules as $m ) {
	require_once MWM_DIR . '/inc/' . $m . '.php';
}
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once MWM_DIR . '/inc/seed/cli.php';  // 8. seeder never loads on front-end
}
```

Rules: no module queries the DB at include time; CPTs on `init` (priority 10), ACF on `acf/init`, Elementor classes only inside `elementor/widgets/register` (because `Widget_Base` does not exist before Elementor loads), seeder only under CLI or its admin screen.

---

## 3. Architectural Patterns

### Pattern 1: One markup source — widget → template part (KEEP, tightened)

**What:** Each widget's `render()` calls `get_template_part( 'template-parts/sections/{x}', null, $args )`. PHP fallback templates call the same part with their own args. Templates read every arg through `mwm_arg( $args, $key, $fallback )`: key absent → fallback (live data / neutral UI label); key present but `''` → element hidden.
**When:** Every section. No widget prints markup itself; no `content_template()` (JS templates would duplicate markup — rely on server render, which Elementor Free does for widgets without `content_template()`).
**Trade-offs:** Editor re-renders via AJAX (slightly slower live preview) in exchange for zero markup drift between editor, front-end, and fallback. Worth it.

**Tightening for MWM:**
- Template docblock lists every arg (Al-Masa did this — keep it, it is the contract between widget and template).
- Neutral UI microcopy (button "اطلب عرض سعر", filter "الكل") may be translatable fallbacks in templates; **headlines/paragraphs never** — they come from seeded Elementor data or CPT fields.

### Pattern 2: Declarative content specs (CHANGE — kills `template_args()` boilerplate)

**What:** Widgets declare content fields; the base class registers controls and maps settings → args using one set of semantics.

```php
abstract class Mwm_Widget extends \Elementor\Widget_Base {
	use Mwm_Content_Fields, Mwm_Style_Box, Mwm_Style_Text, Mwm_Style_Button,
	    Mwm_Style_Card, Mwm_Style_Media, Mwm_Style_Grid;

	protected $template = '';                                  // 'template-parts/sections/hero'
	abstract protected function content_fields(): array;       // sections → fields
	protected function style_parts(): array { return array(); }
	protected function extra_args( array $s ): array { return array(); } // repeaters etc.

	public function get_categories(): array { return array( 'mwm' ); }
	public function has_widget_inner_wrapper(): bool { return false; }
	public function get_style_depends(): array { return array( 'mwm-w-' . $this->slug() ); }
	public function get_script_depends(): array { return array( 'mwm-theme' ); }

	final protected function register_controls(): void {
		$this->register_content_fields( $this->content_fields() ); // trait
		$this->register_style_parts( $this->style_parts() );       // traits
	}
	final protected function render(): void {
		$s = $this->get_settings_for_display();
		get_template_part( $this->template, null,
			array_merge( $this->collect_args( $s ), $this->extra_args( $s ) ) );
	}
}

// Field kinds and their arg semantics (implemented once in the trait):
//  'copy'    TEXT/TEXTAREA, default ''      → '' = hidden; value = shown (seeded copy lives in _elementor_data)
//  'dynamic' TEXT, default '', placeholder = live value (page title, site name…)
//            → '' = key OMITTED (template uses live data); auto "show_{key}" switcher: off = key ''
//  'media'   MEDIA, default empty           → no id = key OMITTED (template uses featured/linked image)
//  'link'    'link_source' SELECT (custom | page role | whatsapp_1 | whatsapp_2 | phone_1 | email)
//            + URL control shown only for "custom" → resolved URL string
//  'toggle'  SWITCHER → bool;  'number' / 'select' → typed value
```

Example widget becomes ~25 lines:

```php
class Mwm_Widget_Page_Hero extends Mwm_Widget {
	protected $template = 'template-parts/sections/page-hero';
	public function get_name(): string { return 'mwm-page-hero'; }
	public function get_title(): string { return __( 'MWM — رأس الصفحة', 'mwm' ); }
	protected function is_dynamic_content(): bool { return true; } // reads post title/thumbnail
	protected function content_fields(): array {
		return array( 'content' => array( __( 'المحتوى', 'mwm' ), array(
			'kicker' => array( 'copy',    __( 'عنوان صغير', 'mwm' ) ),
			'title'  => array( 'dynamic', __( 'العنوان', 'mwm' ), 'live' => 'post_title' ),
			'lead'   => array( 'copy',    __( 'الوصف', 'mwm' ), 'textarea' => true ),
			'image'  => array( 'media',   __( 'صورة الخلفية', 'mwm' ), 'live' => 'post_thumbnail' ),
			'crumbs' => array( 'toggle',  __( 'مسار التنقل', 'mwm' ), 'default' => true ),
		) ) );
	}
	protected function style_parts(): array {
		return array(
			'section' => array( 'box',  __( 'القسم', 'mwm' ),   '.mwm-page-hero' ),
			'overlay' => array( 'media', __( 'الطبقة', 'mwm' ), '.mwm-page-hero__media' ),
			'kicker'  => array( 'text', __( 'العنوان الصغير', 'mwm' ), '.mwm-page-hero__kicker' ),
			'title'   => array( 'text', __( 'العنوان', 'mwm' ), '.mwm-page-hero__title' ),
			'lead'    => array( 'text', __( 'الوصف', 'mwm' ),   '.mwm-page-hero__lead' ),
		);
	}
}
```

**Why `placeholder` instead of `default` for live values:** Elementor's editor serializes with `removeDefault`, but the code path explicitly *keeps* TEXT/TEXTAREA controls that have a value (`if (r.save_default || (type is text|textarea) && value) return;`). A dynamic default is therefore persisted the first time the client saves → frozen. Placeholder shows the live value without being stored. **[VERIFY]** in the first widget spike by saving a page and inspecting `_elementor_data`.

**Trade-offs:** Slightly more abstraction in the base class; but 15+ widgets × ~30 lines of mapping removed, and semantics can't drift between widgets. Keep an escape hatch (`extra_args()`, or override `register_controls` pieces) for repeaters/galleries.

### Pattern 3: Style parts via traits + CSS custom properties (CHANGE — "full style control on every widget")

**What:** `style_parts()` returns `key => [ type, label, selector ]`; each type is a trait method that adds a Style-tab section with a stable control-ID prefix (`{key}_…`). All selectors are `{{WRAPPER}} {selector}`.

| Part type | Controls added (responsive where it matters) |
|---|---|
| `box` | Group_Control_Background (classic/gradient incl. image), padding & margin (responsive DIMENSIONS), min-height (responsive SLIDER), Group_Control_Border, border-radius, Group_Control_Box_Shadow, content alignment |
| `text` | color (+ hover color where link), Group_Control_Typography, Group_Control_Text_Shadow, text-align (responsive CHOOSE), margin-bottom (responsive), responsive "show on" switchers (`selectors_dictionary` → `display:none`) |
| `button` | typography, padding, radius, border, `start_controls_tabs` normal/hover: text color, background, border color; icon size/gap |
| `card` | background, border, radius, shadow, padding, hover: background/shadow/translate (transform only) |
| `media` | aspect-ratio (SELECT → `aspect-ratio`), object-fit/position, radius, overlay color + opacity (writes `--mwm-overlay`, `--mwm-overlay-opacity`), CSS filters |
| `grid` | columns (responsive NUMBER → `--mwm-cols`), gap (responsive SLIDER → `--mwm-gap`), slider-vs-grid layout select |
| `icon` | size, color, background, padding, radius |

**Use CSS custom properties as the control→CSS contract** for structural settings: the control writes `{{WRAPPER}} .mwm-grid { --mwm-cols: {{VALUE}}; }` and component CSS does `grid-template-columns: repeat(var(--mwm-cols, 3), minmax(0, 1fr))`. Controls never need to know internal markup, and markup can be refactored without breaking saved data.

**Do not duplicate the Advanced tab.** Elementor Free already gives every widget wrapper margin/padding, background, border, entrance animations, transform and responsive hide (desktop/tablet/mobile). Style parts target **inner** elements only. Because `has_widget_inner_wrapper()` is `false`, Advanced background applies to the widget element itself — design section templates so the outer `<section>` sits flush inside it.

**Specificity rule:** theme component CSS uses single-class BEM selectors (`.mwm-hero__title`). Elementor's generated selectors (`.elementor-123 .elementor-element.elementor-element-abc .mwm-hero__title`) are always more specific → client overrides always win, and theme defaults apply when controls are empty. **Never wrap theme CSS in `@layer`** — unlayered Elementor CSS would then beat every theme rule regardless of specificity.

**Control-ID freeze:** control IDs are the persisted data schema. Freeze them before the seeder writes Elementor JSON; renaming afterwards needs a migration.

**Kit defaults:** leave color/typography controls **empty by default** (no per-instance CSS output). Theme CSS consumes tokens that are bridged to Kit variables (Pattern 6), so widgets follow the Kit until the client overrides a single instance. Use the `global => [ 'default' => … ]` control argument only where an element must *visibly* show the Kit binding in the panel (e.g., primary button background), because every bound control emits CSS per instance.

### Pattern 4: Schema-driven global settings (CHANGE — one source for contacts)

**Decision: custom admin page with the Settings API, not the Customizer, not an ACF "settings post".**
- Customizer: loads a full preview (heavy with Elementor), no structured multi-slot UI, scattered sections (Al-Masa had two separate sections already).
- Hidden singleton post + ACF Free Group fields: works, but shows up in post lists, has a title/trash, and contact data would vanish if ACF were deactivated.
- Settings API page: one top-level menu «إعدادات MWM», tabs, capability-gated, sanitized, survives without ACF, ~250 lines driven by a schema.

```php
function mwm_settings_schema(): array {
	return array(
		'contact' => array( 'label' => __( 'التواصل', 'mwm' ), 'fields' => array(
			'phones'      => array( 'type' => 'slots', 'count' => 4, 'fields' => array(
				'label' => 'text', 'number' => 'phone', 'whatsapp' => 'bool', 'call' => 'bool' ) ),
			'email'       => array( 'type' => 'email' ),
			'wa_message'  => array( 'type' => 'text' ),        // prefilled wa.me text
		) ),
		'company' => array( 'label' => __( 'بيانات المنشأة', 'mwm' ), 'fields' => array(
			'legal_name_ar' => array( 'type' => 'text' ), 'legal_name_en' => array( 'type' => 'text' ),
			'cr_number' => array( 'type' => 'text' ), 'vat_number' => array( 'type' => 'text' ),
		) ),
		'social'   => array( 'label' => __( 'السوشيال ميديا', 'mwm' ), 'fields' => array( /* url per network */ ) ),
		'leads'    => array( 'label' => __( 'الطلبات', 'mwm' ), 'fields' => array(
			'notify_emails' => array( 'type' => 'emails' ) ) ),
		'floating' => array( 'label' => __( 'أزرار التواصل العائمة', 'mwm' ), 'fields' => array(
			'enabled' => array( 'type' => 'bool' ), 'side' => array( 'type' => 'select', 'options' => array( 'start', 'end' ) ) ) ),
		'layout'   => array( 'label' => __( 'الهيدر والفوتر', 'mwm' ), 'fields' => array(
			'header_template' => array( 'type' => 'elementor_template' ),
			'footer_template' => array( 'type' => 'elementor_template' ) ) ),
		'pages'    => array( 'label' => __( 'صفحات الموقع', 'mwm' ), 'fields' => array(
			'role_home' => array( 'type' => 'page' ), 'role_about' => array( 'type' => 'page' ),
			'role_services' => array( 'type' => 'page' ), 'role_gallery' => array( 'type' => 'page' ),
			'role_licenses' => array( 'type' => 'page' ), 'role_branches' => array( 'type' => 'page' ),
			'role_quote' => array( 'type' => 'page' ), 'role_contact' => array( 'type' => 'page' ) ) ),
	);
}
// The same schema drives: admin rendering, sanitize callback, seeder defaults,
// Elementor 'link_source' options, JSON-LD, and the editor data notes.
function mwm_setting( string $path, $default = '' ) { /* dot-path read from static-cached option, then */
	return apply_filters( 'mwm_setting', $value, $path ); // hook point for future Polylang/WPML strings
}
```

**What is NOT in settings:** branch data (CPT `mwm_branch`), license numbers (CPT `mwm_license`). Settings hold only company-wide singletons. Footer/schema read licenses from the CPT → one source, no duplication.

**Exposing settings to Elementor Free:**
- **Do not plan on custom Dynamic Tags.** Official docs: "the basic version of Elementor does not support active dynamic tags" — the framework is in core, but tags are a Pro feature.
- MWM widgets read settings directly (header, footer, CTA band, contact info, floating contact).
- Every MWM button/link uses the **`link_source` select** (custom URL · page role · WhatsApp 1/2 · Call 1/2 · Email) so a client never pastes a phone number into a link.
- Shortcodes for core widgets: `[mwm_phone slot="1"]`, `[mwm_email]`, `[mwm_wa slot="2" text="…"]` — Elementor's Text Editor widget runs shortcodes; the Shortcode widget is in Free.

### Pattern 5: Page-role registry (CHANGE)

`mwm_page_id( 'quote' )` reads `mwm_settings.pages.role_quote`; seeder fills it; admin can re-point via dropdowns. Used by: link defaults, breadcrumbs, PHP fallback section map (`front-page.php`/`page.php` select sections by role, not title), schema `url`s, form redirect, ACF location rules (register a custom ACF location type `mwm_page_role` via `acf_register_location_type` if any page-level fields are needed). Static-cached, zero queries beyond the autoloaded option.

### Pattern 6: Token bridge to the Elementor Kit (CHANGE)

```css
/* tokens.css — the Kit is the single editable source; hex values are fallbacks only */
:root {
  --mwm-leaf:      var(--e-global-color-mwm_leaf,      #5BBF75); /* shapes/accents only (fails AA as text) */
  --mwm-green:     var(--e-global-color-primary,       #28864B);
  --mwm-deep:      var(--e-global-color-secondary,     #194223);
  --mwm-text:      var(--e-global-color-text,          #3A423C);
  --mwm-sky:       var(--e-global-color-mwm_sky,       #A5D7FC);
  --mwm-royal:     var(--e-global-color-accent,        #1A2BC3);
  --mwm-paper:     var(--e-global-color-mwm_paper,     #EAEAEA);
  --mwm-font-display: var(--e-global-typography-primary-font-family, "Readex Pro"), system-ui, sans-serif;
}
```

Elementor stores Kit colors in the active kit's `_elementor_page_settings` (`system_colors` = primary/secondary/text/accent, `custom_colors`), and each entry's `_id` becomes `--e-global-color-{_id}`. The seeder writes **all four system colors plus custom colors with fixed, human `_id`s** (`mwm_leaf`, `mwm_sky`, `mwm_paper`) — note the known quirk that a fresh default kit may lack `system_colors` until saved, so write the full arrays rather than patching. Same for `system_typography`/`custom_typography` and `container_width`. Also set options: `elementor_disable_color_schemes=yes`, `elementor_disable_typography_schemes=yes`, `elementor_font_display=swap`, Google Fonts loading off (`elementor_google_font=0` **[VERIFY]**), and register the self-hosted family into Elementor's font list via the `elementor/fonts/additional_fonts` filter **[VERIFY]** so Kit typography can select it without a CDN request.

### Pattern 7: Header/footer without Theme Builder (KEEP chain, improve)

Resolution chain in `mwm_render_site_part( $slot )`:

```
1. elementor_theme_do_location( $slot )   → only exists with Pro; harmless no-op in Free
2. is_singular('elementor_library')       → print nothing (editing a template itself)
3. mwm_setting("layout.{$slot}_template") → published elementor_library post →
   Plugin::instance()->frontend->get_builder_content_for_display( $id )
4. fallback: get_template_part( 'template-parts/site/' . $slot )   (same partial the header/footer WIDGETS render)
```

Improvements over Al-Masa:
- **Enqueue the chosen templates' CSS in `<head>`** (`\Elementor\Core\Files\CSS\Post::create( $id )->enqueue()` on `wp_enqueue_scripts`) instead of letting `get_builder_content_for_display( $id, true )` print the stylesheet mid-body → avoids FOUC/CLS on every page. **[VERIFY]** API name against installed Elementor version.
- Template choice lives in the MWM settings page (one place), seeded automatically.
- **Header style as an Elementor document setting** (page settings panel: Auto / Transparent / Solid) via `elementor/documents/register_controls` or the `elementor/element/wp-page/document_settings/after_section_end` injection, read with `$document->get_settings()`. "Auto" keeps Al-Masa's first-widget detection (`mwm-hero`/`mwm-page-hero` → transparent).
- Register `elementor/theme/register_locations` anyway (no-op in Free, future-proof if Pro is bought).
- The seeder creates the saved templates (`_elementor_template_type = section` **and** the `elementor_library_type` taxonomy term `section`) containing the `mwm-header` / `mwm-footer` widgets.

### Pattern 8: Data model without Repeater/Gallery/Options (NEW for MWM)

| Entity | Storage | Public? | Fields (ACF Free types; field name = meta key) | Consumers |
|---|---|---|---|---|
| **Service line** (3) | CPT `mwm_service`, rewrite `services`, supports title/editor/thumbnail/excerpt/page-attributes, **Elementor-enabled** | Yes — `/services/{latin-slug}/` | `service_summary` (textarea), `service_icon` (select from sprite), `license` (post_object → `mwm_license`), `cta_label` (text, optional) | Home services, services page, service single, quote form select, schema `Service`, gallery tabs |
| **Sub-service item** (~18) | CPT `mwm_service_item`, supports title/thumbnail/page-attributes (`menu_order`) | **No** (`public => false`, `show_ui => true`) | `service` (post_object → `mwm_service`, return ID — required), `item_summary` (textarea), `item_icon` (select), `item_featured` (true_false) | service-items widget, service cards (counts/first N), ticker, quote form sub-select |
| **Gallery** | Per-service ordered attachment IDs in meta `mwm_gallery` (custom wp.media metabox, same serialized-ID format ACF Pro Gallery uses → zero migration if Pro is added) + optional item thumbnails | via service | caption/alt from Media Library (seeded Arabic alt) | gallery widget (tabs = services), service single gallery |
| **Branch** (Jazan, Riyadh, Dammam) | CPT `mwm_branch`, page-attributes order | **No** in v1 (anchors `#jazan` on branches page); one-line switch to public city pages later | `region` (text), `address` (textarea), `map_query` (text), `phone_1`/`phone_2` (text, optional; fall back to global phones), `email` (email), `hours` (text), `is_main` (true_false) — **no lat/lng until provided** | branches widget + maps, footer, contact page, LocalBusiness per branch |
| **License** (2 fields, NCEC) | CPT `mwm_license` | **No** | `issuer` (text, seeded NCEC), `scope` (select: rehabilitation \| pollution-control filters), `license_number` (text, empty → hidden), `issued`/`expires` (date_picker), `document` (image or file), `service` (post_object) | licenses widget, service single badge, footer trust line, schema |
| **Request** (quote / site visit) | CPT `mwm_request`, `create_posts => do_not_allow` | **No** | meta: `type` (quote\|site_visit), `name`, `company`, `phone`, `email`, `city`, `service_id` + `service_label` snapshot, `item_label`, `preferred_date`, `site_details`, `message`, `source_url`, `_mwm_status` (new\|contacted\|closed) | admin inbox (columns, status filter, unread bubble), email notification |

**Sub-items modeling decision:** separate non-public CPT linked by an ACF `post_object` field. Rejected alternatives:
- *Hierarchical `mwm_service` children* — children get public URLs (`/services/parent/child/`) with thin content, pollute sitemaps, and need redirect hacks.
- *Textarea "one item per line"* — no per-item image/summary/order; the PDF maps photos to specific items (bag-filter spares p.11, valves p.12, ducts p.14, fans p.15, odor filters p.16–17), so items need thumbnails.
- *ACF Group with fixed N slots* — rigid, wasteful UI.
Admin UX: list column "الخدمة", dropdown filter by service, order via `menu_order` (Page Attributes box), data note in widgets linking to the filtered list.

**Page-local lists are widget repeaters, not CPTs:** sectors served (crushers, block/ready-mix plants, factories, mining, roads authorities, commercial buildings), "why MWM" points (grounded: NCEC-licensed in 2 fields, own filter workshops, trained installation/maintenance team, specialist ventilation engineers, warranted spare parts, 3 branches/Kingdom-wide), process steps. Elementor Free supports `Controls_Manager::REPEATER` in custom widgets.

### Pattern 9: Service detail pages = Elementor-editable CPT singles with PHP fallback (DECISION)

- Add `mwm_service` to `elementor_cpt_support` (filter + one-time option update, as Al-Masa did). Elementor Free edits CPT singles; only Theme-Builder *single templates* are Pro.
- `single-mwm_service.php`: if `mwm_uses_elementor_layout()` → `the_content()` inside theme `<main id="main">`; else render the section map (page-hero → service-items → gallery(current) → licenses(filtered) → cta-band → quote-form(preselected service)).
- Data-fed widgets resolve "current service" via `mwm_context_post_id()` (editor, preview iframe, AJAX re-render, front) — default source "current service", overridable to a specific service.
- Seeder builds each of the 3 service posts' Elementor layout with those widgets and the PDF copy.
- Rejected: separate Pages per service linked by ACF — duplicates identity (two titles/slugs/thumbnails), breaks breadcrumbs and schema. Rejected: PHP-only singles — violates "client edits everything in Elementor".
- Use the theme template (not Elementor's "Full Width" `elementor_header_footer` template) so the `<main>` landmark, skip-link target and breadcrumbs stay theme-controlled; set `_wp_page_template = default` in seeds.
- URL shape: page role "services" at `/services/` (Elementor page) and singles at `/services/{slug}/` coexist (CPT rewrite rules precede the page catch-all). Prefer short Latin slugs (`rehabilitation`, `pollution-control-filters`, `ventilation-systems`) with Arabic titles — final call belongs to the SEO research.

### Pattern 10: Versioned idempotent seeder (CHANGE)

```
wp mwm seed [--step=<name>] [--force]          (WP-CLI via C:\xampp\php\php.exe wp-cli.phar)
Tools → «تهيئة المحتوى» (manage_options + nonce)  — fallback when WP-CLI is unavailable

Steps (each has a version in option mwm_seed_state; re-run only when bumped):
 1. options      blogname/description, locale ar, permalinks /%postname%/, timezone Asia/Riyadh,
                 mwm_settings defaults (phones +966573825004/+966573832990 both WhatsApp, email)
 2. media        import docs/source/photos/** per manifest.json → attachments (alt, caption,
                 _mwm_seed_key = "media:services/p11-1"); logo → custom_logo; skip if key exists
 3. content      licenses → services → service items → branches (lookup by _mwm_seed_key,
                 create if missing); galleries assigned from manifest; thumbnails
 4. elementor    Kit globals (colors/typography/container width) + Elementor options + cpt_support
 5. templates    header/footer elementor_library posts (+ taxonomy term) → settings.layout
 6. pages        8 pages with Elementor JSON built from seed/data/pages.php → page roles,
                 front page = role_home
 7. singles      Elementor JSON for the 3 service posts
 8. menus        primary + footer menus from page roles + service singles
 9. finalize     flush rewrite rules, Elementor files_manager->clear_cache()
```

Rules that make it safe to re-run:
- **Entity identity = `_mwm_seed_key` meta**, never titles (Al-Masa used deprecated `get_page_by_title()` and inserted projects unconditionally).
- **Client-edit guard:** store `_mwm_seed_hash` (hash of what the seeder wrote). On re-run: current hash == seed hash → untouched → may update to the new seed version; different → client edited → skip and report. `--force` overrides.
- **Deterministic Elementor element IDs** (`substr( md5( $seed_key . '/' . $path ), 0, 7 )`) → stable CSS files/anchors across re-seeds.
- Write Elementor meta correctly: `_elementor_data` = `wp_slash( wp_json_encode( $tree ) )` (unslashed JSON gets corrupted by `update_post_meta`), `_elementor_edit_mode = builder`, `_elementor_template_type = wp-page` (pages) / `wp-post` (CPT singles) / `section` (library), `_elementor_version = ELEMENTOR_VERSION`. Use containers (`elType: container`) — default layout model in current Elementor.
- **Seed only copy and choices into widget settings; never seed values that should follow the dashboard** (page title, featured image, service data) — otherwise they get pinned (see Pattern 2).
- Copy lives in `inc/seed/data/content.php` (Arabic, grounded in PROJECT.md facts only) — not in widget defaults.
- Media extraction is a separate dev tool (`tools/extract-pdf-images.mjs`, Node 24 — e.g. pdf.js image-object extraction + sharp re-encode; see STACK.md) producing full-resolution photos + `manifest.json` (file → service, item, PDF page, Arabic alt). The seeder only consumes the manifest, so extraction can be re-run or hand-corrected without touching PHP.

### Pattern 11: JS module registry with editor-safe re-init (KEEP idea, harden)

```js
const MWM = { modules: [] };
MWM.register = (selector, init) => MWM.modules.push({ selector, init });
function initScope(scope = document) {
  for (const { selector, init } of MWM.modules) {
    const nodes = [...scope.querySelectorAll(selector)];
    if (scope.matches?.(selector)) nodes.unshift(scope);
    for (const el of nodes) if (!el.dataset.mwmReady) { el.dataset.mwmReady = '1'; init(el, ctx); }
  }
}
// ctx: { reduceMotion, isEditor: !!window.elementorFrontend?.isEditMode?.(), onScroll, onResize, io }
```

- Hook `frontend/element_ready/widget` (as Al-Masa) — editor AJAX re-renders replace the DOM, so the registry re-inits the new node.
- **Fix Al-Masa's leak:** `initHeader` added a `window` scroll listener per init; in the editor each re-render added another. Use one shared rAF-throttled scroll/resize dispatcher and one shared `IntersectionObserver`; handlers drop instances whose element is no longer `isConnected`.
- In the editor: disable autoplay, show `[data-reveal]` content immediately (otherwise sections look empty while editing), keep sliders interactive.
- Modules: header (sticky/transparent, mobile nav with focus trap + Esc), slider (CSS scroll-snap + controls, RTL-aware direction), reveal, gallery (filters + `<dialog>` lightbox), quote form (shared rules from `wp_localize_script`), floating contact (number picker popover, `aria-expanded`), counters only for derived numbers.
- `elementor-frontend` is NOT a dependency of theme.js (keeps PHP-fallback pages free of Elementor JS); hook lazily via `elementor/frontend/init`.

### Pattern 12: Head-time asset pre-scan (NEW, perf)

- Global: `tokens.css`, `base.css`, `core.css` (shell/buttons/section head/header/footer/floating contact), `motion.css`, `theme.js` (deferred), self-hosted fonts with `<link rel="preload">` for the primary weight only.
- Per section: `mwm-w-{slug}` handles registered globally. On `wp_enqueue_scripts`, read the current document's `_elementor_data` (+ chosen header/footer templates), collect `widgetType` values starting with `mwm-`, enqueue matching handles in `<head>`; cache the list in post meta keyed by `post_modified`. PHP fallback templates declare their sections (`mwm_use_sections( array( 'hero', 'services' ) )`) before `get_header()`.
- `get_style_depends()` stays declared so the editor preview iframe always gets the CSS even before the pre-scan cache exists.
- If this proves fiddly, the acceptable fallback is a single minified bundle — but measure first; unused-CSS is a common Lighthouse deduction.

---

## 4. Data Flow

### 4.1 Request / render flow

```
GET /services/pollution-control-filters/
   ↓
single-mwm_service.php → get_header()
   ↓                          ↓
   │              header.php: <html dir=rtl> · wp_head (core CSS, pre-scanned widget CSS,
   │                          header/footer template CSS, JSON-LD, OG) · skip link
   │                          → mwm_render_site_part('header')
   │                               → settings.layout.header_template → Elementor template
   │                                    → mwm-header widget → template-parts/site/header
   │                                         ← mwm_setting(phones/socials), menu location
   ↓
mwm_uses_elementor_layout()? ──yes──▶ the_content() → Elementor renders container tree
   │                                     → Mwm_Widget::render()
   │                                         settings ─(content specs)─▶ args
   │                                         get_template_part(sections/x, args)
   │                                              ← mwm_arg(args, key, live fallback)
   │                                              ← data helpers (items, gallery, licenses)
   │                                     Elementor post CSS ({{WRAPPER}} overrides) in head
   └──no──▶ PHP section map (same template parts, args from page role / current post)
   ↓
get_footer() → footer part (same chain) → floating contact (settings) → wp_footer (theme.js)
   ↓
Browser: theme.js initScope(document); in editor: element_ready → initScope(widgetEl)
```

### 4.2 Global settings flow (single source → many consumers)

```
MWM Settings page (Settings API, manage_options, nonce)
   ↓ sanitize via mwm_settings_schema() (phones → E.164, emails, URLs)
wp_options: mwm_settings (autoload, one row)
   ↓ mwm_setting( 'contact.phones.0.number' )  (static cache, filter 'mwm_setting')
   ├─▶ header / footer template parts (tel:, wa.me, socials, CR/VAT)
   ├─▶ floating contact (WhatsApp picker lists slots with whatsapp=true)
   ├─▶ widget link_source resolver (buttons anywhere)        ├─▶ shortcodes in core widgets
   ├─▶ leads/handler (notify_emails, fallback admin_email)    ├─▶ JSON-LD Organization/contactPoint
   └─▶ editor data notes ("الأرقام تأتي من إعدادات MWM ← تعديل")
```

### 4.3 Structured data flow

```
WP admin (CPT edit screens, ACF Free fields, gallery metabox)
   ↓ save_post → postmeta (ACF name = meta key; gallery = ordered ID array)
data/queries.php (mwm_services(), mwm_service_items($service_id), mwm_gallery_ids($service_id),
                  mwm_branches(), mwm_licenses($scope)) — static-cached, ACF optional
   ├─▶ widgets/templates (read-only)
   ├─▶ quote form <select> (services → items)
   └─▶ seo/schema-graph (Service, LocalBusiness ×3, credentials text)
```

### 4.4 Lead flow (write path)

```
quote-form template (nonce, honeypot "company_site", type quote|site_visit, service preselect)
   ↓ theme.js: validate with mwm_form_rules() (localized: regex + Arabic messages)
   ↓ fetch POST admin-ajax.php?action=mwm_lead      (no-JS: admin-post.php → redirect ?mwm_form=sent|error#quote)
leads/handler.php: nonce → honeypot (fake success) → server validation (same rules, Arabic-Indic digits → ASCII,
                   Saudi phone pattern) → per-IP transient throttle → wp_insert_post(mwm_request, meta, _mwm_status=new)
   ↓                                        ↓
wp_mail(notify_emails, Reply-To visitor)   admin inbox: columns, status filter, unread bubble, read-only details
   ↓
JSON { ok, message, errors{} } → inline status (aria-live) / field errors (aria-describedby)

WhatsApp / call taps: direct wa.me / tel: links (no server round-trip); optional
dataLayer.push({event:'mwm_contact', channel}) hook for later analytics.
```

### 4.5 Design-token flow

```
Visual identity → seed/data/kit.php → Elementor Kit (_elementor_page_settings, fixed _ids)
   ↓ Elementor prints :root { --e-global-color-primary … --e-global-color-mwm_leaf … }
tokens.css  --mwm-* : var(--e-global-*, brand fallback)
   ↓
base/core/widget CSS (single-class selectors) ← per-instance overrides from widget style controls
                                                 ({{WRAPPER}}-scoped, higher specificity)
```

### 4.6 Seed flow

```
docs/source/services.pdf ──tools/extract-pdf-images.mjs──▶ docs/source/photos/** + manifest.json
PROJECT.md facts ──(hand-written)──▶ inc/seed/data/*.php (copy, services, items, branches, pages, kit)
   ↓ wp mwm seed
options → media → content (licenses, services, items, branches, galleries) → Kit → templates
→ pages (Elementor JSON, page roles, front page) → service singles → menus → finalize (rewrite + Elementor cache)
```

---

## 5. Suggested Build Order (dependencies)

Matches the client's chosen horizontal-layers order; arrows = "must exist before".

```
A. Foundation ──▶ B. Data model ──▶ C. Widget framework ──▶ D. Sections/widgets ──▶ E. Seed & pages ──▶ F. SEO/perf/a11y/QA
                     ▲                                              ▲
  tools/extract-pdf-images (parallel, any time before E) ──────────┘ (manifest needed by E only)
```

| Order | Build | Depends on | Why this position |
|---|---|---|---|
| A1 | Theme skeleton, bootstrap, setup, core helpers (`mwm_arg`, phone utils), `header.php`/`footer.php` shell, `page.php`/`front-page.php`/`index.php`/`404.php` | WP + Arabic locale | Everything renders through these |
| A2 | Tokens (Kit bridge), base/core CSS, self-hosted fonts, theme.js registry skeleton | A1 | Every template part depends on tokens/components |
| A3 | **Settings schema + Settings page + page-role registry** | A1 | Header/footer/floating/CTA/form all read it; doing it later forces rewiring every consumer |
| A4 | PHP header/footer partials + floating contact | A2, A3 | Site is navigable and convertible before Elementor work |
| B1 | CPTs + ACF local groups + gallery metabox + admin UX | A1 | Widgets and seeder need the model; ACF keys become data schema |
| B2 | Data query layer (`data/`) | B1 | Single read API for widgets, fallbacks, schema, form |
| B3 | Lead pipeline backend (request CPT, rules, handler, notifications, privacy) | A3, B2 | Independent of Elementor; testable with a raw form |
| C1 | Elementor bootstrap: category, `Mwm_Widget` base, content-spec trait, **all style-part traits**, link_source resolver, document header-style control, header/footer resolution + head CSS | A3, A4 | **Must be complete before the first real widget** — retrofitting style controls across 15 widgets is the most expensive rework in Al-Masa's pattern |
| C2 | Prove the framework on 2 widgets (page-hero = dynamic fields; services = data-fed grid/slider), spike the [VERIFY] items (placeholder vs default persistence, head CSS enqueue, fonts filter, pre-scan) | C1, B2 | De-risks the abstraction before scaling it |
| D1 | Chrome widgets: header, footer, cta-band, contact-info | C2 | Needed by every page and by the seeded templates |
| D2 | Content widgets: hero, intro, services, service-items, sectors (repeater), why (repeater), ticker, stats (derived counts only) | C2, B2 | Home + about + services |
| D3 | Data-heavy widgets: gallery (tabs/filters/lightbox), licenses, branches (+ map embeds from `map_query`/address) | C2, B2 | Depend on gallery metabox and CPT data |
| D4 | Quote/site-visit form widget (front end of B3) | B3, C2 | Final conversion piece |
| D5 | `single-mwm_service.php` + fallback section maps by role | D2–D4 | Needs the section set complete |
| — | **Freeze widget names + control IDs** | D1–D5 | Seeded JSON depends on them |
| E1 | PDF photo extraction + manifest (can start any time after A1) | — | Only needed for seeding |
| E2 | Seeder: options → media → content → Kit → templates → pages → singles → menus | Freeze, E1 | Builds the real site; re-runnable |
| F | JSON-LD/OG/sitemaps, performance pass (pre-scan, image sizes, preloads), a11y pass, responsive + Elementor editability QA | E2 | Needs real content to measure |

**Research flags by phase:** C2 (Elementor save/placeholder behavior, CSS file API, fonts filter — test against the installed version), E1 (PDF image extraction on Windows/Node with no Python/poppler — STACK.md), E2 (Kit meta structure in the installed Elementor version), F (LocalBusiness-per-branch markup without addresses — SEO research). A1–B3 and D1–D4 are standard patterns proven in Al-Masa.

---

## 6. Scaling Considerations

Realistic scale: a corporate lead-gen site — hundreds to low thousands of visits/day, dozens of requests/month.

| Scale | Architecture adjustments |
|---|---|
| Launch (≤ 5k visits/day) | Monolith theme is right. Page cache at host level (or a single cache plugin); Elementor CSS print method = external files; images sized via `add_image_size` + WebP/AVIF |
| Growth (more services/projects) | Add `mwm_project` CPT when the client supplies named projects (gallery widget already supports a source switch); branch CPT → public city pages for local SEO |
| Bilingual | `mwm_setting` filter + Polylang/WPML string registration; seeded pages duplicated per language; templates already translatable (`mwm` domain) |

### Scaling priorities
1. **First bottleneck: editor/registration cost, not traffic.** Data notes and dynamic placeholders run queries inside `register_controls()`; guard them with `is_editor_request()` (Al-Masa did this for notes) and static caches. Front-end cost must stay ~zero.
2. **Second: spam on the form** → honeypot + throttle are in; add a time-to-submit check before reaching for CAPTCHA (CAPTCHA hurts conversions and Lighthouse).
3. **Third: uncached widgets** → mark static widgets `is_dynamic_content() === false` so Elementor element caching applies; data-fed widgets stay dynamic (they invalidate correctly only if uncached).

---

## 7. Anti-Patterns

### Anti-Pattern 1: Dynamic values as control defaults
**What people do:** `'default' => get_the_title( $page_id )` on a TEXT control.
**Why it's wrong:** TEXT/TEXTAREA values are persisted on save even when equal to the default → the page silently stops following the dashboard; defaults also run queries on every control registration.
**Do this instead:** empty default + `placeholder` with the live value + template fallback; separate "show" switcher for hiding.

### Anti-Pattern 2: Looking things up by Arabic title
**What people do:** `get_page_by_title( 'تواصل معنا' )`, ACF location by title-resolved ID, `page.php` switch on title.
**Why it's wrong:** client renames a page → links/sections break; `get_page_by_title` is deprecated; extra queries per request.
**Do this instead:** page-role registry; `_mwm_seed_key` for seeded entities.

### Anti-Pattern 3: Hand-rolled style sections per widget
**What people do:** copy-paste color/typography blocks with slightly different IDs and coverage per widget.
**Why it's wrong:** inconsistent editing experience ("why can I change the button color here but not there?"), and every new requirement means editing N widgets.
**Do this instead:** style-part traits, declared selector maps, CSS custom properties as the contract.

### Anti-Pattern 4: Contact data in more than one place
**What people do:** phones in a CPT, socials in Customizer, a WhatsApp number typed into a button link, license numbers in settings *and* a CPT.
**Why it's wrong:** the client updates one copy, the others go stale (and schema ≠ visible NAP hurts local SEO).
**Do this instead:** singletons in `mwm_settings`, records in CPTs, every link via `link_source`.

### Anti-Pattern 5: Relying on Pro-only mechanisms that "almost" work in Free
**What people do:** register custom Dynamic Tags, Theme Builder conditions, ACF Options pages/Repeaters, Elementor Pro Form widget.
**Why it's wrong:** Dynamic Tags aren't active in Free; the others don't exist → features silently missing for the client.
**Do this instead:** widget-level `link_source` + shortcodes; saved-template header/footer chain; Settings API page; CPTs + widget repeaters; theme-owned form.

### Anti-Pattern 6: Seeding by hand or by one-shot script
**What people do:** build pages manually in the editor; a seed script guarded by one global flag.
**Why it's wrong:** not reproducible on staging/production, no way to add content later without duplicates, client edits overwritten on re-run.
**Do this instead:** versioned steps, seed keys, hash guard, deterministic element IDs.

### Anti-Pattern 7: `@layer` or `!important` to "beat" Elementor
**Why it's wrong:** layered theme CSS loses to all unlayered Elementor CSS; `!important` blocks client overrides from widget controls.
**Do this instead:** load theme CSS after `elementor-frontend` (Al-Masa did), single-class selectors, let generated `{{WRAPPER}}` selectors win.

### Anti-Pattern 8: Markup in two places
**What people do:** a `content_template()` JS template for live preview plus PHP `render()`; or a PHP fallback page with its own markup.
**Why it's wrong:** drift; the editor shows something different from production.
**Do this instead:** server render through template parts only.

### Anti-Pattern 9: Inventing data to fill layouts
**What people do:** placeholder stats, coordinates, license numbers, client logos.
**Why it's wrong:** violates the content-integrity constraint and erodes B2B trust.
**Do this instead:** every optional field hides its UI when empty; stats only from derived counts (3 branches, 3 service lines, 2 licensed fields).

---

## 8. Integration Points

### External services

| Service | Integration pattern | Notes |
|---|---|---|
| WhatsApp | `https://wa.me/9665XXXXXXXX?text=` built by `mwm_wa_url()` from settings slots | Visitor picks number in floating popover; prefilled message from settings; `rel="noopener"` |
| Phone | `tel:+9665…` via `mwm_tel_href()`; display local format inside `dir="ltr"` span | Saudi normalization: `05…` / `5…` / `9665…` / `009665…` → `+9665…` |
| Google Maps | Keyless embed iframe from `map_query` or address, `loading="lazy"`, click-to-load facade recommended for perf | Never invent coordinates; no map until an editor fills a location |
| Email (`wp_mail`) | Notify `leads.notify_emails` (default `mwmksa92@gmail.com`), Reply-To visitor | Production needs SMTP plugin/provider; Gmail sender reputation — document in ops notes |
| SEO plugins (later) | `mwm_seo_plugin_active()` disables theme OG/JSON-LD parts that overlap | Theme schema remains source for LocalBusiness/branches if plugin lacks it |
| Translation plugin (later) | `mwm_setting` filter, `mwm` text domain, per-language Elementor docs | No English content in v1 |

### Internal boundaries

| Boundary | Communication | Notes |
|---|---|---|
| Elementor layer ↔ template parts | `$args` array only (documented in each template docblock) | Templates never call Elementor APIs; widgets never echo markup |
| Template parts ↔ data | `data/` functions + `mwm_setting()` only | No raw `WP_Query` in templates |
| Admin (CPT/ACF/settings) ↔ front end | DB only (meta keys = ACF field names) | ACF optional at read time (`mwm_get_field()` falls back to `get_post_meta`) |
| Form JS ↔ PHP handler | `mwm_form_rules()` localized once; AJAX JSON `{ok,message,errors}` | Same rules both sides — no drift |
| Seeder ↔ everything | Writes through public WP/Elementor APIs and the same option/meta keys the runtime reads | Seeder depends on frozen widget names/control IDs |
| Theme ↔ Elementor Kit | Kit CSS variables consumed by `tokens.css` | Fixed Kit `_id`s are part of the contract |

---

## Sources

- **Al-Masa theme source (read directly, HIGH):** `C:\xampp\htdocs\al-masa\PROJECT_RULES.md`; `wp-content/themes/almasa/functions.php`, `inc/elementor.php`, `inc/elementor/class-almasa-widget.php`, `inc/elementor/widgets-sections.php`, `inc/elementor/widgets-layout.php`, `inc/helpers.php`, `inc/contact-form.php`, `inc/seed-once.php`, `inc/acf.php`, `inc/social.php`, `inc/setup.php`, `inc/enqueue.php`, `template-parts/sections/hero.php`, `template-parts/sections/services.php`, `template-parts/header/header.php`, `page.php`, `front-page.php`, `assets/js/theme.js`, `assets/css/tokens.css`
- **Elementor developer docs (official):** Dynamic Tags — "the basic version of Elementor does not support active dynamic tags" (https://developers.elementor.com/docs/dynamic-tags/); Widget Dependencies `get_style_depends`/`get_script_depends` (https://developers.elementor.com/docs/widgets/widget-dependencies/); Widget output caching `is_dynamic_content()` and DOM optimization `has_widget_inner_wrapper()` (https://developers.elementor.com/docs/widgets/widget-output-caching/, https://developers.elementor.com/docs/widgets/widget-inner-wrapper/, Elementor 3.22 developers update); Global Style control `global => default` and `__globals__` storage (https://developers.elementor.com/docs/editor-controls/global-style, https://developers.elementor.com/docs/data-structure/global-styles)
- **Elementor issue tracker / source (MEDIUM):** Kit `system_colors`/`custom_colors` `_id` → `--e-global-color-{_id}`, default-kit quirk (github.com/elementor/elementor issues #12843, #12571); `_elementor_data` stores only non-default values (issue #8114, maintainer reply); editor `toJSON({removeDefault})` keeps TEXT/TEXTAREA values (editor bundle source) — **[VERIFY] against installed version**
- **ACF (official):** Free vs PRO — Repeater, Gallery, Flexible Content, Clone, Options Pages, Blocks are PRO; Group, Post Object, Relationship, Date Picker, Image/File are free (https://www.advancedcustomfields.com/resources/frequently-asked-questions/)
- **Unverified, flagged [VERIFY]:** `elementor/fonts/additional_fonts` filter and `elementor_google_font` option for self-hosted fonts; `Core\Files\CSS\Post::create()->enqueue()` for head-time template CSS; head enqueue behavior of widget style deps for documents rendered mid-body

---
*Architecture research for: Arabic RTL corporate WordPress theme (Elementor Free + ACF Free), MWM environmental services*
*Researched: 2026-10-04*
