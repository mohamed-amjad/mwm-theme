# Stack Research

**Domain:** Arabic RTL-first corporate WordPress site — custom classic PHP theme `mwm` + Elementor Free + ACF Free (Saudi B2B environmental services, lead generation)
**Researched:** 2026-10-04
**Confidence:** HIGH overall. Versions were checked against official registries and the machine itself. The Elementor API names were checked in the Elementor 4.3 plugin source installed on the sister project.

**Confidence legend used below**
- **HIGH** means one of: checked in an official registry or API (wordpress.org plugin API, npm registry, GitHub releases API, Packagist); inspected on this machine (`php -v`, `wp-includes/version.php`, GD info, plugin headers); read in the Elementor/ACF source on disk; or measured directly (font file sizes, the PDF image probe).
- **MEDIUM** means a web search result cross-checked against official docs, or an inference from verified facts.
- **LOW** means a single unverified source. LOW items are flagged and should not be treated as authoritative.

---

## TL;DR decisions

| Decision | Choice | Confidence |
|---|---|---|
| WordPress | **7.1.x** (7.1.1 already installed locally). Theme header `Requires at least: 6.8`, `Tested up to: 7.1` | HIGH |
| PHP | Develop on **8.3** (local 8.3.35). Write code compatible with **8.2–8.5**. Production on **8.3 or 8.4** (WordPress recommends 8.4+). Theme header `Requires PHP: 8.2` | HIGH |
| Page builder | **Elementor Free 4.3.x** (4.3.3, 2026-09-30). Custom widgets use the **classic `\Elementor\Widget_Base` API**, not Atomic widgets | HIGH (versions) / MEDIUM (Atomic decision) |
| Custom fields | **ACF Free 6.8.x** (6.8.10, 2026-09-10). Field groups registered in PHP with `acf_add_local_field_group()` plus `acf-json/` sync | HIGH |
| Global settings | A theme admin page built on the **WordPress Settings API**, stored in one `mwm_settings` option. Not ACF (options pages are Pro) and not the Customizer | HIGH |
| Fonts | **Alexandria** for headings/display (variable wght 100–900) + **Readex Pro** for body/UI (variable wght 160–700). Both OFL-1.1, self-hosted woff2 from **Fontsource 5.3.0** (arabic + latin subsets only) | HIGH (facts) / MEDIUM (aesthetic match) |
| CSS/JS | **No bundler.** Vanilla CSS (custom properties, logical properties, native nesting, `@layer`, `clamp()`, container queries) and one deferred vanilla JS file. Optional one-shot minify with Lightning CSS | HIGH |
| Sliders / lightbox / motion | Native CSS scroll-snap + small JS controls. `<dialog>` lightbox. `IntersectionObserver` reveal. Popover API for the WhatsApp number picker. **No Swiper, GSAP or GLightbox** | HIGH |
| SEO | The theme emits a JSON-LD `@graph` (Organization, WebSite, one LocalBusiness per branch, Service, BreadcrumbList), Open Graph and meta description. It uses **WordPress core sitemaps** and backs off automatically if Yoast or Rank Math is installed | HIGH |
| PDF photos | **MuPDF.js (`mupdf` 1.28.1)** Node script copies the embedded JPEG streams byte-for-byte, then **WP-CLI `wp media import`**. WordPress generates WebP sub-sizes itself | HIGH |
| Forms | **Native theme form**: nonce, honeypot, time-trap, per-IP throttle, `wp_mail`, private CPT inbox. **FluentSMTP 2.4.1** in production, **Mailpit 1.31.4** locally | HIGH |
| Dev tooling | WP-CLI 2.12.0 phar, Composer 2.10.3 phar, PHPCS 3.13.x + WPCS 3.4.1 + PHPCompatibilityWP 2.1.8, Playwright 1.63, Lighthouse 13.5 on Node 24.18 | HIGH |

---

## Environment facts (inspected on this machine, 2026-10-04)

| Item | Value | Implication |
|---|---|---|
| WordPress core | `7.1.1` (`wp-includes/version.php`) | Latest branch. 7.1 shipped 2026-08-19 |
| PHP CLI | `8.3.35` ZTS x64 (built 2026-09-22) | 8.3 is security-only until 2027-12-31. Fine for development |
| PHP extensions | gd, intl, mbstring, curl, openssl, zip, exif, fileinfo, mysqli, Phar; **no imagick** | WordPress uses **GD** as its image editor |
| GD | bundled 2.1.0, **WebP: yes, AVIF: yes**, FreeType | `image_editor_output_format` can produce WebP (and AVIF) locally |
| php.ini | memory_limit 1024M, upload 5G, mail `SMTP=localhost`, `smtp_port=25` | Point mail at Mailpit (port 1025) for local capture |
| DB | MariaDB **10.4.32** (end of life) | Fine locally. Production should be MariaDB 10.11/11.4 LTS or MySQL 8.4 |
| Web server | Apache 2.4.58 | Enable `mod_deflate` + `mod_expires` in `.htaccess` so Lighthouse runs resemble production |
| Node / npm | **v24.18.0** / 11.16.0 | Runs all tooling. No Python, so pyftsubset and fonttools are out |
| Git | `C:\Program Files\Git\cmd\git.exe` (not on PATH) | Call it by full path or prepend to PATH per session |
| Not installed | wp-cli, composer, poppler (`pdfimages`), mutool, ImageMagick, scoop, choco | **winget** is available. Everything below avoids needing admin installs |
| Sister project | Elementor **4.3.2** + ACF **6.8.10** running Al-Masa's classic widgets | The classic widget API works on Elementor v4 in practice |

---

## Recommended Stack

### Core Technologies

| Technology | Version | Purpose | Why Recommended | Conf. |
|---|---|---|---|---|
| WordPress | 7.1.x (auto-update minors) | CMS, media, menus, CPTs, sitemaps, image sub-sizes | Only the newest branch gets maintenance ("Only the most recent in the 7.1 series is safe to use"). Elementor 4.3.3 and ACF 6.8.10 are both tested up to 7.1.2 | HIGH |
| PHP | 8.3 (dev) / 8.3–8.4 (prod). Code targets 8.2+ | Runtime | WordPress 7.x is fully compatible with 8.2–8.5, and the hosting handbook recommends 8.4+ for production. 8.2 reaches EOL on 2026-12-31, so the floor is 8.2. Saudi shared hosts commonly offer 8.2–8.4 | HIGH |
| MariaDB / MySQL | Prod: MariaDB 10.11+ or MySQL 8.4. Local: 10.4.32 | Database | WordPress needs 5.5.5+, but 10.4 is EOL. Use `utf8mb4` / `utf8mb4_unicode_520_ci` (WordPress default) for correct Arabic sorting | HIGH |
| Custom classic theme `mwm` | — | HTML shell, CPT/ACF registration, tokens, widgets, SEO, forms, a11y, performance | Proven on Al-Masa. Elementor Free has no Theme Builder, so header, footer, singles and archives must be theme-owned. A block theme would fight Elementor | HIGH |
| Elementor (Free) | **4.3.3** (requires WP 6.8+, PHP 7.4+) | Visual editing of every section through custom "MWM" widgets, Kit global colors and fonts, flexbox containers | Client-mandated. v4 keeps full support for classic v3 widgets next to Atomic elements. Changelog: "Both new and existing sites can combine legacy Widgets and Atomic Elements on the same page" | HIGH |
| ACF (Free) | **6.8.10** (requires WP 6.2+, PHP 7.4+) | Structured fields on CPTs (services, projects/gallery, branches, licenses) | Client-mandated. Local field groups in PHP are version-controlled and deploy with the theme | HIGH |

### Elementor Free 4.3: what to use and how

**Widget API.** Use classic `\Elementor\Widget_Base` (confirmed on developers.elementor.com and in the 4.3 source):
- Registration: `add_action( 'elementor/widgets/register', fn( $m ) => $m->register( new MWM_Widget_Hero() ) );`
- Category: `elementor/elements/categories_registered` → `$elements_manager->add_category( 'mwm', [ 'title' => 'MWM', 'icon' => ... ] )`. Keep Al-Masa's closure trick to move the category to the top. Elementor has no API for category order.
- Methods: `get_name/get_title/get_icon/get_categories/get_keywords`, `get_style_depends(): array`, `get_script_depends(): array`, `has_widget_inner_wrapper(): bool` (return `false`; Optimized Markup is on for new sites), `is_dynamic_content(): bool` (return `true` for widgets that read CPT/ACF/settings, so Element Caching never serves stale data), `register_controls()`, `render()`. Skip `content_template()` and let PHP render. One markup source stays the template part, as in Al-Masa.
- Controls (`\Elementor\Controls_Manager`): `TEXT, TEXTAREA, WYSIWYG, URL, MEDIA, SWITCHER, SELECT, CHOOSE, NUMBER, SLIDER, DIMENSIONS, COLOR, ICONS, GALLERY, REPEATER, RAW_HTML, HEADING, DIVIDER`. **`REPEATER` and `GALLERY` controls are free in Elementor.** They replace ACF Pro Repeater/Gallery for section-level lists such as hero slides, feature bullets, gallery tabs and "why us" items. Repeatable *business records* (services, branches, licenses, projects) still belong in CPTs.
- Group controls: `Group_Control_Typography`, `Group_Control_Background` (types `classic`, `gradient`; video is Pro-oriented), `Group_Control_Border`, `Group_Control_Box_Shadow`, `Group_Control_Text_Shadow`, `Group_Control_Css_Filter`, `Group_Control_Image_Size`. Use them through `$this->add_group_control( Group_Control_Typography::get_type(), [ 'name' => ..., 'selector' => '{{WRAPPER}} .mwm-hero__title' ] )`.
- Responsive: `add_responsive_control()` for padding, gap, columns, alignment, font size and visibility. Use `selectors` with `{{WRAPPER}}`. Never inline styles in `render()`.
- **Bind defaults to the Kit** so the client changes brand colors once (constants verified in source):

```php
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;      // COLOR_PRIMARY = 'globals/colors?id=primary'
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;  // TYPOGRAPHY_PRIMARY = 'globals/typography?id=primary'

$this->add_control( 'title_color', [
    'label'     => esc_html__( 'Title color', 'mwm' ),
    'type'      => Controls_Manager::COLOR,
    'global'    => [ 'default' => Global_Colors::COLOR_PRIMARY ],
    'selectors' => [ '{{WRAPPER}} .mwm-section__title' => 'color: {{VALUE}};' ],
] );
$this->add_group_control( Group_Control_Typography::get_type(), [
    'name'     => 'title_typo',
    'global'   => [ 'default' => Global_Typography::TYPOGRAPHY_PRIMARY ],
    'selector' => '{{WRAPPER}} .mwm-section__title',
] );
```

**Kit (Site Settings) seeding.** This is an improvement over Al-Masa, which set the Kit by hand. Do it in the idempotent seed-once routine (APIs verified in `core/kits/manager.php` and `core/base/document.php`):

```php
$kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
$kit->update_settings( [
    'system_colors'     => [ /* _id: primary|secondary|text|accent → #28864B, #194223, #3A423C, #5BBF75 */ ],
    'custom_colors'     => [ /* leaf, off-white #EAEAEA, sky #A5D7FC, royal #1A2BC3 … */ ],
    'system_typography' => [ /* primary → Alexandria 700, secondary → Alexandria 600, text → Readex Pro 400, accent → Readex Pro 500 */ ],
    'container_width'   => [ 'unit' => 'px', 'size' => 1280 ],
    'default_generic_fonts' => 'Tahoma, "Segoe UI", sans-serif',
] );
\Elementor\Plugin::$instance->files_manager->clear_cache();
```

**Fonts inside Elementor without Google.** Two changes, both verified in `includes/fonts.php` / `frontend.php`:
1. `update_option( 'elementor_google_font', '0' )` disables the Google Fonts integration. Then `add_filter( 'elementor/frontend/print_google_fonts', '__return_false' )` as a second guard.
2. `add_filter( 'elementor/fonts/additional_fonts', fn( $f ) => $f + [ 'Alexandria' => 'system', 'Readex Pro' => 'system' ] )`. Elementor merges with `array_replace()`, so mapping these families to `system` makes them selectable in every Typography control while Elementor never enqueues them. The theme's self-hosted `@font-face` serves them.

**Elementor settings/features to set** (seed through options where possible, verify under Elementor → Settings):

| Setting | Value | Why | Conf. |
|---|---|---|---|
| Flexbox Container (and Grid) | On (default) | Sections/columns are legacy. Build each page from one full-width container per MWM widget | HIGH |
| Optimized Markup | On (default for new sites) | Removes `elementor-widget-container` wrappers. Our widgets return `has_widget_inner_wrapper() false` | HIGH |
| Element Caching | On | Static widgets are faster. Dynamic MWM widgets opt out through `is_dynamic_content()` | HIGH |
| Lazy Load Background Images | On | Free LCP/bytes win for Elementor background controls | HIGH |
| Inline Font Icons (SVG) | On | Avoids the Font Awesome/eicons font downloads | HIGH |
| `elementor_load_fa4_shim` | `''` (off) | No legacy FA4 shim | HIGH |
| `elementor_css_print_method` | `external` | Cacheable CSS files instead of inline blobs | HIGH |
| `elementor_disable_color_schemes` / `elementor_disable_typography_schemes` | `yes` | The theme and Kit win, not Elementor defaults (same as Al-Masa) | HIGH |
| `elementor_font_display` | `swap` | Consistent with the theme's `@font-face` | HIGH |
| Load Google Fonts Locally | Off / irrelevant | Google Fonts disabled entirely | HIGH |
| Active breakpoints (`active_breakpoints`) | Mobile ≤767, Tablet ≤1024, **Laptop ≤1366**. Leave Mobile-Extra and Widescreen off | Every active breakpoint multiplies the CSS generated per responsive control and adds a device the client must check. 1440/1920 are handled with `clamp()` and the max container width | MEDIUM |
| **Atomic Editor (v4 elements)** | **Disable** for this site (Elementor → Editor → Settings → Atomic Editor) | One editing model for the client: the v3 panel matches every MWM widget. v3 Global Colors/Fonts stay the single source of truth, and the client never sees two "Heading" widgets. Can be re-enabled later with no data loss | MEDIUM |

**Do not build MWM widgets as Atomic widgets** (`Atomic_Widget_Base`, Twig templates, props schema). The API exists, but it is new and mainly documented through Elementor's in-repo skill docs. Its generic class-based Style tab is a poorer fit for "every text, color, spacing per section" than classic per-widget controls, and Al-Masa's approach is already proven on 4.3.2. Revisit for v2. (MEDIUM)

**Elementor still loads jQuery** on the frontend (`elementor-frontend` depends on `jquery-ui-position`, verified). Theme JS must not add more jQuery code. Hook into the editor with `elementorFrontend.hooks.addAction( 'frontend/element_ready/widget', ( $scope ) => init( $scope[0] ) )` and use vanilla DOM inside.

### ACF Free 6.8

| Aspect | Recommendation | Conf. |
|---|---|---|
| Registration | `acf_add_local_field_group()` in `inc/acf.php` on `acf/include_fields`, keys prefixed `field_mwm_*` / `group_mwm_*`. Also set `acf/settings/save_json` and `acf/settings/load_json` to `get_stylesheet_directory() . '/acf-json'` so UI edits round-trip into git | HIGH |
| Field types available in Free | Text, Textarea, Number, Range, Email, URL, Password, Image, File, WYSIWYG, oEmbed, Select, Checkbox, Radio, Button Group, True/False, Link, Post Object, Page Link, Relationship, Taxonomy, User, Google Map, Date / Date-Time / Time Picker, Color Picker, Icon Picker, Message, Accordion, Tab, **Group** | HIGH |
| Pro-only (do not use) | Repeater, Flexible Content, Gallery, Clone, Options Pages, ACF Blocks | HIGH |
| CPT registration | `register_post_type()` in theme code, **not** ACF's post-type UI. Code is versioned and survives ACF deactivation | HIGH |
| Gallery substitute | Native attachment-ID-array metabox (Al-Masa's `project_gallery` pattern), or an Elementor `GALLERY` control when the gallery is section-level | HIGH |
| Output | Read through a theme wrapper (`mwm_field( $key, $post_id )`) that falls back to `get_post_meta()` when ACF is inactive. The site must not fatal without ACF | HIGH |
| Global contacts (phones, WhatsApp, email, CR/VAT, licenses, socials) | Settings API admin page "إعدادات الموقع" → `get_option( 'mwm_settings' )` behind `mwm_setting( 'whatsapp_1' )`. This feeds header, footer, floating buttons and schema, and Elementor widgets prefill from it | HIGH |

### Fonts: matching 29LT Bukra

Bukra, per the foundry: "contemporary geometric … mono-linear … Arabic letterforms based on the Kufic structure with large open proportions and short ascenders and descenders … Latin … large main body height". The closest free match must be geometric, monoline, wide, with a large x-height Latin.

Measured Fontsource 5.3.0 woff2 sizes (downloaded and measured today):

| Family | Type | Arabic subset | Latin subset | Character vs Bukra | Verdict |
|---|---|---|---|---|---|
| **Alexandria** (Mohamed Gaber, Montserrat's Arabic companion) | Variable wght 100–900 | **30.6 KB** (all weights) | 29.4 KB | Geometric, monoline, wide, horizontal emphasis. Montserrat-style Latin with a large x-height, which is very close to Bukra's Latin | **Headings / display / buttons** |
| **Readex Pro** (Chahine & Jockin, Lexend method) | Variable wght 160–700 (+HEXP, not shipped) | **22.3 KB** | 30.7 KB | Geometric, low contrast, wide, designed for reading fluency | **Body / UI** |
| IBM Plex Sans Arabic | Static (each weight separate) | 41.8 KB **per weight** | 18.7 KB/weight | Humanist and narrower, a corporate-tech feel that doesn't read as Bukra (Al-Masa used it) | Reject: off-brand and heavy across several weights |
| Noto Kufi Arabic | Variable 100–900 | **120.8 KB** | 30.0 KB | Kufic structure, the closest Arabic skeleton | Reject: 4× the bytes |
| Tajawal | Static 7 weights | 8.7 KB/weight | 10.0 KB | Geometric but small glyph set, weak Latin, no variable | Reject: dated, weak Latin |
| Almarai | Static 4 weights | 30.9 KB/weight | 17.1 KB | Simple and neutral, no geometric "tomorrow" feel | Reject |
| Rubik (Arabic) | Variable | 31.7 KB | 34.5 KB | Rounded corners, softer and playful. The wrong tone for "air-quality experts" | Reject |
| Beiruti / Fustat (also checked) | Variable | 55.8 KB / — | 41.9 KB | Beiruti is geometric but heavier. Fustat is manuscript-Kufi, a calligraphic tone | Reject |

**Decision (MEDIUM: aesthetic judgement on verified facts):**
- **Headings:** Alexandria. Weights 600 (h3/h4, buttons), 700 (h1/h2), 800 (hero display only).
- **Body:** Readex Pro. Weights 400 (body), 500 (emphasis, nav), 600 (labels).
- One variable file per family per subset covers every weight, so there is no per-weight cost.
- **Cost:** ~113 KB total (4 files). The Latin file always downloads on any page, because U+0020 (space), Western digits and punctuation sit in the Latin `unicode-range`.
- **Lean fallback if Lighthouse font budget fails:** Alexandria only (≈60 KB). Its designer explicitly targets long text at light weights.

**Implementation (HIGH):**
- Get the files with `npm pack @fontsource-variable/alexandria @fontsource-variable/readex-pro` in `tools/`. Copy only the `*-arabic-wght-normal.woff2` and `*-latin-wght-normal.woff2` files to `wp-content/themes/mwm/assets/fonts/` with each family's `LICENSE` (OFL-1.1). Drop `latin-ext` and `vietnamese`.
- Write `@font-face` in `tokens.css` with the Fontsource `unicode-range` values, `font-weight: 100 900` / `160 700`, and `font-display: swap`.
- `<link rel="preload" as="font" type="font/woff2" crossorigin>` **only** `alexandria-arabic-wght-normal.woff2` (the hero H1 font).
- Fallback stack: `"Segoe UI", Tahoma, "Geeza Pro", "Noto Sans Arabic", sans-serif`. Add a metric-adjusted fallback `@font-face` (`size-adjust` / `ascent-override` on `local("Tahoma")`) to cut swap CLS. Tune the values during the foundation phase (MEDIUM).
- **Do not glyph-subset the Arabic files further** (subset-font / harfbuzz). Arabic shaping needs the init/medi/fina/liga GSUB lookups, the files are already 22–31 KB, and aggressive subsetting breaks editor-entered text later.
- Don't call Google Fonts CDN (Al-Masa did). It's an extra origin, privacy exposure, and render-blocking CSS.

### Frontend: CSS, JS and motion

| Concern | Choice | Why | Conf. |
|---|---|---|---|
| Build step | **None.** Hand-written CSS/JS served as-is. Optional `npx lightningcss-cli@1.33 --minify` before deploy | WordPress enqueues files directly, the client never builds, and modern CSS features need no transpiling. Brotli/gzip makes minification marginal | HIGH |
| RTL | RTL-first with **logical properties** (`margin-inline-start`, `inset-inline-end`, `padding-block`, `text-align: start`), `dir` from the WordPress locale. **No `style-rtl.css`, no RTLCSS flip** | The same stylesheet works for the future English (LTR) version | HIGH |
| CSS architecture | `tokens.css` (custom properties: brand colors, fluid type scale with `clamp()`, 4px spacing), `base.css`, `layout.css` (header, footer, container, buttons) loaded globally. **One CSS file per section widget**, registered with `wp_register_style()`, returned from that widget's `get_style_depends()`, and enqueued by the PHP fallback path for fallback pages. Use `@layer tokens, base, layout, components, utilities` so theme CSS has a predictable cascade against Elementor | Al-Masa shipped a single 51 KB `site.css` to every page | MEDIUM: verify in QA that widget styles print in `<head>`; if not, fall back to one minified bundle |
| Modern CSS used freely | Native nesting, `:has()`, container queries, `aspect-ratio`, `inset`, `gap`, `clamp()`, `color-mix()`, `scroll-snap`, `overscroll-behavior`, `prefers-reduced-motion` | All Baseline in current browsers | HIGH |
| JS delivery | One `assets/js/theme.js` (target < 12 KB min), `wp_enqueue_script( …, [ 'strategy' => 'defer', 'in_footer' => true ] )` (WP 6.3+). Idempotent `init( scope )` re-run on `frontend/element_ready/widget` | Al-Masa's pattern, now deferred | HIGH |
| Sliders | CSS `scroll-snap` track + arrow/progress/autoplay controls in vanilla JS (port Al-Masa's `slider_controls`). Arrows mirror correctly in RTL because they use `scrollBy` with `inline` direction awareness | No library and swipe is native on touch. If a real carousel effect is ever required, **reuse Elementor's bundled Swiper v8 (`swiper` handle)** via `get_script_depends()` instead of adding Swiper 14 | HIGH |
| Reveal motion | `IntersectionObserver` adds `.is-in`, and CSS transitions animate `transform` and `opacity` only. `@media (prefers-reduced-motion: reduce)` disables it | Universal support and ~1 KB | HIGH |
| Scroll-linked flourishes | CSS scroll-driven animations (`animation-timeline: view()`) **only inside `@supports`**, for decorative parallax and progress | Chrome/Edge 115+, Safari 26+, Firefox 156+, ~85% global. Not Baseline, so never essential | MEDIUM |
| Lightbox | Native `<dialog>` with `showModal()`, prev/next, Esc/arrow keys, focus return, `srcset` inside | Built-in top layer and inert background. ~2–3 KB vs GLightbox (stale since Jan 2025) | HIGH |
| WhatsApp picker (both numbers) | `popover` attribute + `popovertarget` button. The two `wa.me/9665…` links come from settings | Works with zero JS, keyboard and light-dismiss built in (Popover API Baseline 2024) | HIGH |
| Page transitions | Optional `@view-transition { navigation: auto; }` progressive enhancement | Free polish where supported; ignored elsewhere | LOW (support details not verified today) |

### SEO without paid plugins

| Concern | Choice | Why | Conf. |
|---|---|---|---|
| Structured data | `inc/schema.php` prints one `<script type="application/ld+json">` `@graph`: `Organization` (`@id` `/#org`: name AR/EN, logo, email, telephone ×2, `sameAs` from settings, `areaServed: SA`), `WebSite`, **one `LocalBusiness` per Branch CPT** (`parentOrganization` → `/#org`, `PostalAddress` with `addressLocality`/`addressRegion` (جازان، الرياض، المنطقة الشرقية), `addressCountry: SA`, branch phone, and `geo` **only when an editor fills coordinates**), `Service` on each service page (`provider` → `/#org`, `areaServed`), `BreadcrumbList` on inner pages | Multi-location LocalBusiness is **Rank Math PRO-only** and Yoast Local SEO is paid. The theme already owns the Branch CPT data, and no invented data means empty fields omit properties | HIGH |
| Schema type | `LocalBusiness` (generic). Do not use `ProfessionalService`, which schema.org marks as confusing/legacy and Google gives no extra treatment | No environmental-services subtype exists | MEDIUM |
| Meta & social | Theme prints `<meta name="description">`, `og:*`, `twitter:card=summary_large_image`, `og:locale=ar_SA`. Values come from a small "SEO" ACF group per page/CPT (title override, description, OG image), falling back to excerpt and featured image, with a 1200×630 default OG image from settings | Client-editable, no plugin | HIGH |
| Plugin coexistence | If `defined( 'WPSEO_VERSION' )` or `class_exists( 'RankMath' )`, skip the theme's meta/OG/Organization output and keep the branch LocalBusiness nodes | Prevents duplicate markup if the client installs one later | HIGH |
| Sitemap | WordPress core `wp-sitemap.xml` (since 5.5). Filter `wp_sitemaps_add_provider` to drop `users` (stops author enumeration). Filter `wp_sitemaps_post_types` to drop `post` (no blog). Non-public CPTs (quote inbox, licenses if not public) are excluded automatically | Zero-plugin, standards-compliant | HIGH |
| Title | `add_theme_support( 'title-tag' )` + `document_title_parts` filter honoring the SEO override | Core behavior | HIGH |

### Image pipeline (PDF photos → media library)

**Probe result (MuPDF.js on `docs/source/services.pdf`, run today):** 17 pages, **30 image XObjects**.
- 29 are **DCTDecode (original JPEG streams)**. They can be extracted byte-identical, with no re-encoding.
- One is a Flate 1192×1685 page-1 background/frame, not a photo.
- Pages 1–8 (rehabilitation and spills): **1280×960** (one 1600×1200).
- Pages 9–17 (filters, spares, ventilation): **small, 225–1040 px**.

| Step | Tool | Notes | Conf. |
|---|---|---|---|
| 1. Extract | `tools/extract-pdf-images.mjs` using **`mupdf` 1.28.1** (official Artifex WASM build, no native install) | For each page `Resources/XObject` with `Subtype /Image`: if `Filter` is `DCTDecode`, write `obj.readRawStream()` to `.jpg` (lossless copy). Otherwise render through `PDFImage → Pixmap → asPNG()`. De-duplicate by indirect object number. End the script with `process.exit(0)`: the WASM runtime kept the probe process alive. License AGPL-3.0 is fine for a local dev tool that is never distributed | HIGH |
| 2. Name & map | Rename to `services-pNN-<slug>.jpg` with a JSON manifest (page → service line → Arabic alt text) | Feeds alt text, gallery grouping and service featured images | HIGH |
| 3. Import | **WP-CLI** `wp media import <file> --title="…" --alt="…" --porcelain`, then attach and set meta in the seed routine | Gives proper attachments, `srcset` and sub-sizes | HIGH |
| 4. Formats | Theme filter `image_editor_output_format`: `image/jpeg → image/webp`. WordPress keeps the original JPEG as `full` and writes WebP sub-sizes. Enable AVIF only if production Site Health → Media shows GD/Imagick AVIF support | WebP is universal in GD builds. AVIF through GD is slower and host-dependent. Upload originals, never pre-converted files, so later client uploads get the same treatment | HIGH (APIs) / MEDIUM (WebP-over-AVIF call) |
| 5. Sizes | Register theme sizes matched to layouts (e.g. `mwm-card` 640×480 hard crop, `mwm-wide` 1280w). Rely on WordPress 6.7+ `sizes="auto"` for lazy images. Hero image: `wp_get_attachment_image( $id, 'full', false, [ 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '100vw' ] )` | Core responsive images | HIGH |
| 6. Don't upscale | Max source width is 1280 px, so full-bleed 1920 heroes would be blurry. Use split/inset hero layouts (photo ≤ ~60% width), a deep-green overlay, or `object-fit: cover` within ≤1280 px. **Ask the client for original photos.** Use page 9–17 images only in cards/thumbnails ≤ 400 px | Content quality risk for the roadmap | HIGH |
| Optional | `sharp` 0.35.5 (Apache-2.0) for the derived assets WordPress can't make: `screenshot.png` 1200×900, the default OG image 1200×630, a leaf-pattern PNG fallback | Dev-only, not for the photo pipeline | HIGH |

Alternatives checked: **pdfimages (poppler)** gives the same lossless result (`pdfimages -all`) but needs the `oschwartz10612/poppler-windows` v26.09.0 zip or a winget install. That's fine as a cross-check, but unnecessary. **pdfjs-dist 6.4** decodes JPEGs to RGBA bitmaps, so re-encoding is lossy and Node needs canvas workarounds.

### Forms and email

| Concern | Choice | Why | Conf. |
|---|---|---|---|
| Quote / site-visit / contact form | **Native theme form**, ported and hardened from Al-Masa `inc/contact-form.php`: `wp_nonce_field`, honeypot field, **time-trap** (reject posts < 3 s after render, signed timestamp), per-IP throttle with a transient keyed on a salted hash of the IP, server-side validation shared with JS through `wp_localize_script` (Saudi mobile `^(?:\+?966|0)?5\d{8}$`), `admin-post.php` no-JS fallback + AJAX enhancement, a **private `mwm_request` CPT inbox** with unread bubble and read-only metabox | Elementor Pro Forms are off-limits. WPForms Lite stores no entries (Pro feature). CF7 loads assets site-wide and has no inbox without Flamingo. Fluent Forms is heavy and hard to make Elementor-styleable. The native form is ~10 KB of PHP the theme fully controls, with styling exposed as Elementor controls | HIGH |
| Spam escalation (only if needed) | Cloudflare Turnstile (free) behind a settings toggle | Privacy-friendlier and lighter than reCAPTCHA | MEDIUM |
| Notification | `wp_mail()` **To:** the notification email from settings (default `mwmksa92@gmail.com`). **From:** `no-reply@<site domain>`. **Reply-To:** the visitor | Sending *From* a `@gmail.com` address through a non-Google server fails DMARC alignment and lands in spam or gets rejected | MEDIUM |
| Production SMTP | **FluentSMTP 2.4.1** (free, no paid tier gating; 700k installs). Use the domain mailbox SMTP or a transactional provider (Brevo/SES) with SPF/DKIM on the domain | Fully free alternative to WP Mail SMTP (4.10.0, whose better mailers are upsold) | HIGH |
| Local mail | **Mailpit v1.31.4** single Windows exe (SMTP :1025, UI :8025). Route mail with a local-only mu-plugin hooking `phpmailer_init` (`isSMTP`, host `127.0.0.1`, port 1025), or php.ini `smtp_port=1025` | Captures every email for QA without sending | HIGH |

### Supporting libraries (dev-time only; nothing ships to visitors)

| Library | Version | Purpose | When to Use |
|---|---|---|---|
| `mupdf` | 1.28.1 | Lossless JPEG extraction from the services PDF | Once, in the content/media phase |
| `@fontsource-variable/alexandria` | 5.3.0 | Source of pre-subset variable woff2 + OFL license | Foundation phase. Copy files into the theme, don't load from node_modules |
| `@fontsource-variable/readex-pro` | 5.3.0 | Same | Foundation phase |
| `@playwright/test` | 1.63.0 | Screenshots at 1920/1440/1366/1280/1024/768/430/390/375/360, horizontal-overflow assertion (`scrollWidth > innerWidth`), RTL checks, keyboard focus walk | Every UI phase + final QA |
| `lighthouse` | 13.5.0 | Mobile performance/a11y/SEO audits (`--preset` default = mobile). Point `CHROME_PATH` at Playwright's Chromium | Per-page budget checks; final QA gate (≥90 mobile) |
| `lightningcss-cli` | 1.33.0 | Optional CSS minify | Pre-deploy only |
| `sharp` | 0.35.5 | Derived images (theme screenshot, OG default) | Occasionally |

### Development Tools

| Tool | Purpose | Notes |
|---|---|---|
| **WP-CLI 2.12.0** (phar) | Install Arabic locale, plugins, activate theme, import media, run seeds, regenerate thumbnails, flush rewrites | `C:\xampp\php\php.exe wp-cli.phar …`. Add a `wp.bat` shim in `tools/`. Put `C:\xampp\mysql\bin` on PATH for `wp db export/import`. 2.12.0 (May 2025) is still the latest stable and is PHP 8.4-compatible |
| **Composer 2.10.3** (phar) | Install PHPCS stack in `tools/` (dev-only, not in the theme) | `php composer.phar …` |
| **PHPCS 3.13.x + WPCS 3.4.1 + PHPCompatibilityWP 2.1.8** | WordPress coding standards, escaping/nonce/i18n sniffs, PHP compatibility | **Do not install PHPCS 4.x** (4.0.4 exists, but WPCS 3.4.1 requires `squizlabs/php_codesniffer ^3.13.5`). WPCS 3.4.1 is a security release, so use ≥3.4.1. `phpcs.xml.dist`: `WordPress-Extra` + `WordPress-Docs` (optional), `prefixes=mwm`, `text_domain=mwm`, `minimum_wp_version=6.8`, `testVersion=8.2-` |
| `php -l` | Fast syntax gate | Loop over theme `*.php` before each commit |
| Playwright + Lighthouse (above) | Responsive and performance QA | Store scripts in `tools/qa/`. Screenshots go in `docs/qa/` (or git-ignored) |
| Mailpit | Email capture | Above |
| Git | Version only theme + docs + tools (same as Al-Masa) | Full path `C:\Program Files\Git\cmd\git.exe` |

---

## Installation

```powershell
# --- WordPress layer (run in C:\xampp\htdocs\mwmpower-ksa) ---
# WP-CLI phar (once). Put it in tools/ and call through PHP
Invoke-WebRequest https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -OutFile tools\wp-cli.phar
$env:PATH = "C:\xampp\mysql\bin;C:\Program Files\Git\cmd;$env:PATH"
function wp { & C:\xampp\php\php.exe "$PWD\tools\wp-cli.phar" @args }

wp core install --url="http://localhost/mwmpower-ksa" --title="MWM" --admin_user=<user> --admin_email=mwmksa92@gmail.com --skip-email
wp language core install ar --activate
wp plugin install elementor advanced-custom-fields --activate      # 4.3.3 / 6.8.10 as of 2026-10-04
wp plugin delete hello akismet
wp theme activate mwm
wp option update elementor_google_font 0
wp option update elementor_load_fa4_shim ''
wp option update elementor_css_print_method external
wp option update elementor_disable_color_schemes yes
wp option update elementor_disable_typography_schemes yes
wp option update elementor_font_display swap
wp rewrite structure '/%postname%/' --hard

# Production only
wp plugin install fluent-smtp --activate

# --- Node dev tooling (tools/package.json, NOT inside the theme) ---
cd tools
npm install -D mupdf@1.28.1 @playwright/test@1.63.0 lighthouse@13.5.0 lightningcss-cli@1.33.0 sharp@0.35.5
npx playwright install chromium
npm pack @fontsource-variable/alexandria@5.3.0 @fontsource-variable/readex-pro@5.3.0   # copy arabic+latin wght woff2 + LICENSE into theme assets/fonts

# --- PHP lint tooling ---
Invoke-WebRequest https://getcomposer.org/download/latest-stable/composer.phar -OutFile composer.phar
& C:\xampp\php\php.exe composer.phar config allow-plugins.dealerdirect/phpcodesniffer-composer-installer true
& C:\xampp\php\php.exe composer.phar require --dev wp-coding-standards/wpcs:"^3.4.1" phpcompatibility/phpcompatibility-wp:"^2.1"
```

---

## Alternatives Considered

| Recommended | Alternative | When to Use Alternative |
|---|---|---|
| Classic `Widget_Base` widgets | Elementor v4 Atomic widgets (`Atomic_Widget_Base` + Twig) | A future redesign where the client adopts v4 Classes/Variables and Elementor publishes stable third-party docs |
| Alexandria + Readex Pro | Alexandria only | Lighthouse font budget fails, or the client prefers one consistent Bukra-like voice (saves ≈53 KB) |
| Alexandria + Readex Pro | IBM Plex Sans Arabic (body) | The client wants a more neutral, corporate-tech body text and accepts ~42 KB per weight |
| Native scroll-snap slider | Elementor's bundled Swiper v8 (`swiper` handle) | A section truly needs fade/coverflow/loop-with-clones effects. It adds no third-party code since Elementor ships it |
| `<dialog>` lightbox | Elementor's built-in lightbox (`data-elementor-open-lightbox="yes"`) | You want zero custom JS and accept Elementor's styling and its eicons dependency |
| Theme JSON-LD + OG | Rank Math Free 1.0.279 / Yoast 28.6 | The client wants a content-score UI and redirects manager. The theme auto-yields meta/OG and keeps the branch schema |
| Native form | Fluent Forms Free | The client later needs conditional multi-step forms built without a developer |
| FluentSMTP | WP Mail SMTP 4.10.0 | The client already uses it elsewhere |
| MuPDF.js extraction | poppler `pdfimages -all` (poppler-windows v26.09.0 zip) | Cross-check the extracted set, or when Node isn't available |
| WebP sub-sizes (core filter) | Performance Lab "Modern Image Formats" 2.7.1 | You want an admin UI to switch WebP/AVIF, fallback JPEGs, or `<picture>` output without code |
| Settings API admin page | Customizer | Only for purely visual toggles. Data (phones, CR, licenses) belongs on an admin page the client can find |

## What NOT to Use

| Avoid | Why | Use Instead |
|---|---|---|
| Google Fonts CDN (`fonts.googleapis.com`) and Elementor's Google font loading | Extra origins, render-blocking CSS, privacy exposure. Al-Masa's main perf debt | Self-hosted Fontsource woff2 + `elementor_google_font=0` + the `additional_fonts` "system" mapping |
| Any font that is static-per-weight for 3+ weights (IBM Plex, Tajawal, Almarai) | Each weight is a separate download | Variable Alexandria / Readex Pro |
| Glyph-subsetting Arabic fonts | Breaks contextual shaping for editor-entered text | Fontsource `unicode-range` split only |
| Swiper 14 / GSAP 3.15 / GLightbox 3.3.1 as theme deps | 25–45 KB+ each for what CSS + ~3 KB of JS does. GLightbox is unmaintained since 2025-01. GSAP is free (Webflow licence) but unnecessary weight against the Lighthouse ≥90 target | Scroll-snap, IntersectionObserver, `<dialog>`, Popover API |
| jQuery in theme JS | Couples the theme to Elementor's legacy dependency | Vanilla DOM. Take `$scope[0]` from Elementor hooks |
| A bundler (Vite/webpack/@wordpress/scripts) | Adds a build the client and future devs must run, and nothing here needs transpiling | Plain files + optional Lightning CSS minify |
| RTLCSS / `style-rtl.css` flipping | The project mandates RTL-first, not a flipped LTR | Logical properties |
| ACF Pro features (Repeater, Gallery, Options page, Flexible Content) | Client has Free only | CPTs, Elementor `REPEATER`/`GALLERY` controls, Settings API page |
| ACF's post-type/taxonomy UI | Config lives in the DB and isn't versioned | `register_post_type()` in `inc/post-types.php` |
| Rank Math/Yoast for multi-branch schema | Multi-location is a paid feature | Theme `@graph` from the Branch CPT |
| Contact Form 7 / WPForms Lite | CF7 has global assets and no inbox. WPForms Lite has no stored entries | Native form + CPT inbox |
| Sending mail *From* `@gmail.com` via the host | DMARC failure → spam/reject | `From: no-reply@domain`, `Reply-To: visitor`, SMTP plugin |
| pdfjs-dist for extraction | Decodes JPEG to raw pixels, so re-encoding is lossy | MuPDF.js raw stream copy |
| Pre-converting photos to WebP before upload | Bypasses WordPress's pipeline, and later client uploads would differ | Upload originals + `image_editor_output_format` |
| PHPCS 4.x with WPCS 3.x | Incompatible (`^3.13.5` required) | PHPCS 3.13.x |
| Calling `__()` / `esc_html__()` before `init` | WordPress 6.7+ just-in-time loading emits "translation loading triggered too early" notices | Register CPT labels and strings on `init` or later |
| Elementor Atomic Editor left on alongside MWM widgets | Two styling systems and duplicate element types confuse the client | Disable Atomic Editor for v1 |

## Stack Patterns by Variant

**If the production host lacks AVIF in GD/Imagick (likely on shared hosting):**
- Keep `jpeg → webp` only.
- WebP support is near-universal in host GD builds, and WordPress can't serve AVIF the host can't encode.

**If Lighthouse mobile < 90 after the widgets phase:**
- First switch body text to Alexandria (drop Readex Pro, −53 KB).
- Then dequeue `elementor-icons` (eicons) on the frontend when no Elementor-native widget needs it.
- Then confirm Element Caching and Lazy Load Backgrounds are on and that only 3 breakpoints are active.
- Elementor's own frontend (jQuery + runtime) is the floor you can't remove, so the theme's share must stay small.

**If the client later buys Elementor Pro:**
- Register real `header`/`footer`/`single`/`archive` locations. The `elementor/theme/register_locations` hook is already present and harmless in Free.
- Keep MWM widgets and stay off Pro Forms, because the CPT inbox is the system of record.

**If the client later buys ACF Pro:**
- Keep meta keys identical (e.g. `project_gallery` as an attachment-ID array) so a Gallery field can take over without migration (Al-Masa precedent).

**If English is added (v2):**
- Logical properties and the `mwm` text domain already cover the theme.
- Add Polylang (free) and Latin weights are already present in both fonts.
- Alexandria's Latin is Montserrat-derived, a strong English heading face.

## Version Compatibility

| Package | Compatible With | Notes |
|---|---|---|
| WordPress 7.1.1 | PHP 7.4–8.5 (8.2–8.5 fully supported) | 7.0 raised the minimum to PHP 7.4 |
| Elementor 4.3.3 | WP ≥ 6.8, PHP ≥ 7.4, tested to WP 7.1.2 | Bundles Swiper v8 (`swiper` handle). Frontend depends on jQuery (`jquery-ui-position`) |
| ACF 6.8.10 | WP ≥ 6.2, PHP ≥ 7.4, tested to WP 7.1.2 | Security release (2026-09-10). Stay current |
| FluentSMTP 2.4.1 / WP Mail SMTP 4.10.0 | Tested to WP 7.1.2 | — |
| Performance Lab 4.2.0 / Modern Image Formats 2.7.1 | WP ≥ 6.9 | Optional only |
| WPCS 3.4.1 | PHPCS ^3.13.5 (**not 4.x**), PHPCSUtils ^1.2.3, PHPCSExtra ^1.5.1, PHP ≥ 7.2 | — |
| PHPCompatibilityWP 2.1.8 | PHPCS 3.x | 3.0 is still alpha. Pin `^2.1` |
| WP-CLI 2.12.0 | PHP ≥ 7.2.24 (8.4 OK), WP ≥ 4.9 | Windows support is "limited" but phar + `php.exe` works for the commands used here |
| mupdf 1.28.1 | Node ≥ 18 (ESM) | AGPL-3.0, dev-only. Call `process.exit()` at the end |
| Playwright 1.63 / Lighthouse 13.5 | Node 24.18 | Use Playwright's Chromium for Lighthouse via `CHROME_PATH` |
| Fontsource 5.3.0 variable packages | Any browser with woff2 + variable fonts (all current) | OFL-1.1 |
| GD (XAMPP PHP 8.3.35) | WebP ✔, AVIF ✔ | Production must be re-checked in Site Health |
| MariaDB 10.4.32 (local) | WP ≥ 5.5.5 requirement satisfied | EOL. Don't mirror in production |

## Sources

- wordpress.org plugin API (`api.wordpress.org/plugins/info/1.2`), queried 2026-10-04: elementor 4.3.3, fluent-smtp 2.4.1, wp-mail-smtp 4.10.0, webp-uploads 2.7.1, performance-lab 4.2.0, seo-by-rank-math 1.0.279, wordpress-seo 28.6. **HIGH**
- https://wordpress.org/plugins/elementor/ + Elementor GitHub `changelog.txt`: 4.3.x changelog, Optimized Markup / Element Caching / Lazy BG / Inline icons / Google-fonts-local history. **HIGH**
- https://elementor.com/products/website-builder/v4-faq/ and https://elementor.com/blog/editor-40-atomic-forms-pro-interactions/: v3 widgets coexist with Atomic, Atomic on by default for new installs, can be disabled. **MEDIUM** (vendor docs via search, consistent with source)
- Elementor 4.3.2 source on disk (`al-masa/wp-content/plugins/elementor`): `Global_Colors::COLOR_PRIMARY`, `Global_Typography::TYPOGRAPHY_PRIMARY`, `kits_manager->get_active_kit()`, `Document::update_settings()`, `files_manager->clear_cache()`, `elementor/fonts/additional_fonts` merged with `array_replace`, `elementor/frontend/print_google_fonts`, options `elementor_google_font`, `elementor_font_display`, `elementor_css_print_method`, `elementor_load_fa4_shim`, `elementor_disable_color_schemes`, `active_breakpoints`, `container_width`, `default_generic_fonts`, the Swiper v8 handle, and frontend jQuery dependency. **HIGH**
- https://developers.elementor.com/docs/widgets/widget-structure and /add-new-widget: Widget_Base methods, `elementor/widgets/register`. **HIGH**
- https://www.advancedcustomfields.com/downloads/ + /changelog/ + wordpress.org ACF page: 6.8.10 (2026-09-10) security release, requirements. **HIGH**
- https://make.wordpress.org/hosting/handbook/server-environment/ and /compatibility/: PHP support matrix, WP 7.1 released 2026-08-19, PHP 8.4+ recommended. **HIGH**
- https://wordpress.org/download/releases/: 7.1 latest branch. **HIGH**
- https://make.wordpress.org/core/2024/02/23/wordpress-6-5-adds-avif-support/, https://make.wordpress.org/core/2024/10/18/auto-sizes-for-lazy-loaded-images-in-wordpress-6-7/: `image_editor_output_format`, AVIF, `sizes="auto"`. **HIGH**
- https://www.29lt.com/product/29lt-bukra/: Bukra design description. **HIGH**
- https://fonts.google.com/specimen/Alexandria, https://fonts.google.com/specimen/Readex+Pro, https://fontsource.org (about pages), https://m3.material.io/blog/readex-pro-legibility-arabic-type-design: font axes, designers, OFL. **HIGH**
- npm registry (`npm view` / `npm pack`, 2026-10-04): Fontsource 5.3.0 packages (woff2 sizes measured locally), swiper 14.3.0, gsap 3.15.0, glightbox 3.3.1, sharp 0.35.5, pdfjs-dist 6.4.299, mupdf 1.28.1 (AGPL-3.0), playwright 1.63.0, lighthouse 13.5.0, lightningcss 1.33.0. **HIGH**
- GitHub releases API: wp-cli v2.12.0, PHP_CodeSniffer 4.0.4, PHPCompatibilityWP 2.1.8, composer 2.10.3, poppler-windows v26.09.0-0, glightbox v3.3.1 (2025-01-21), mailpit v1.31.4. **HIGH**
- https://packagist.org/packages/wp-coding-standards/wpcs + WPCS 3.4.1 release notes: PHPCS ^3.13.5 requirement, security fix. **HIGH**
- https://rankmath.com/kb/multiple-locations/ + /free-vs-pro/: multi-location Local SEO is PRO-only. **HIGH**
- https://gsap.com/community/standard-license/ + GSAP 3.13 blog: GSAP and all plugins free for commercial use. **HIGH** (licence), the not-needed call is ours
- https://caniuse.com/mdn-css_properties_animation-timeline_view + https://webstatus.dev/features/scroll-driven-animations: Chrome 115+, Safari 26+, Firefox 156+, not Baseline. **MEDIUM**
- Local probe `services.pdf` via MuPDF.js (2026-10-04): 30 images, 29 DCT JPEG, max 1280×960 / one 1600×1200, pages 9–17 ≤ 1040 px. **HIGH**
- Gmail/DMARC alignment behavior for spoofed `From: @gmail.com`: industry knowledge, not re-verified today. **MEDIUM**
- `@view-transition` cross-document support details: not verified today. **LOW**

---
*Stack research for: Arabic RTL-first corporate WordPress site (custom classic theme + Elementor Free + ACF Free), MWM Saudi environmental services*
*Researched: 2026-10-04*
