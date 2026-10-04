# Pitfalls Research

**Domain:** Arabic RTL-first custom WordPress theme (`mwm`) + custom Elementor **Free** widgets + ACF **Free** data, for a Saudi B2B environmental-services lead-generation site (WhatsApp / call / quote form), targets Lighthouse mobile 90+, WCAG 2.2 AA, local SEO, full client editability
**Researched:** 2026-10-04
**Confidence:** MEDIUM-HIGH overall. HIGH where the evidence is first-party: Al-Masa code I read, contrast ratios I calculated, and Elementor/SDAIA official docs and source. MEDIUM where it comes from community or secondary sources (flagged inline). The GSD confidence seam labels raw web search as LOW. Every web claim used here was cross-checked against an official page, and the official page is what's cited.

**Phase key used throughout** (the expected roadmap layers):
- **P1**: Foundation (theme shell, tokens, Elementor/WP settings baseline, repo/env)
- **P2**: Data model and global settings (CPTs, ACF, settings screen, form backend)
- **P3**: Elementor widget framework and section widgets
- **P4**: Pages, content and media seeding
- **P5**: SEO, performance and accessibility hardening
- **P6**: QA, deploy and handoff

---

## Lessons inherited from Al-Masa (read first)

`C:\xampp\htdocs\al-masa\PROJECT_RULES.md` already records four lessons. Keep all four:
1. Attachment URLs with spaces break CSS `url()` and `srcset`, so Al-Masa added a `%20` encoding filter. **Root cause:** `seed-once.php` registered files dropped straight into `uploads/` via `wp_insert_attachment()`, which bypasses `sanitize_file_name()`. Fix the cause in MWM: rename files to ASCII kebab-case before import. Don't depend on the filter.
2. Theme CSS vs Elementor default schemes. Disable Elementor default colors/fonts so theme CSS wins.
3. JS must re-init on Elementor `frontend/element_ready/widget`.
4. Header/footer are saved Elementor templates picked in the Customizer, with a PHP fallback.

Reading the Al-Masa theme code turned up more problems that are **not** in its rules. Each one gets a pitfall below:

| # | Al-Masa defect found in code | File | Pitfall |
|---|---|---|---|
| A | Egyptian phone regex `ALMASA_PHONE_PATTERN`, Egyptian placeholder `01XXXXXXXXX`, error text "مثال: 01012345678", `almasa_tel_href()` prefixes `tel:+2` | `inc/contact-form.php`, `inc/helpers.php`, `template-parts/sections/contact-form.php` | 1 |
| B | Page lookups by **title** (`almasa_page_url('مشاريعنا')`, `almasa_page_thumb_id('من نحن')`, etc., in about 15 places). If a page is renamed, CTAs silently fall back to the home page and images vanish | `inc/helpers.php`, widgets, templates | 2 |
| C | Google Fonts from CDN, **9 weights** including 300 (which contradicts its own "no ultra-thin" rule) | `inc/enqueue.php` | 8 |
| D | Hero `<h1>` and CTAs carry `data-reveal`, so they start at `opacity:0` until JS and IntersectionObserver run. That delays LCP | `template-parts/sections/hero.php`, `motion.css` | 9 |
| E | Hero crossfade autoplays forever with no pause control. Marquee runs `36s linear infinite` with no pause, stopping only under reduced motion. Fails WCAG 2.2.2 | `theme.js`, `site.css` | 11 |
| F | Directional chevrons are hard-drawn per RTL (the "previous" icon is a right-pointing path). Lightbox keys are hard-wired (`ArrowLeft` = next). Breaks future English | `helpers.php`, `theme.js` | 6 |
| G | Each `init()` adds `window` resize, `document` visibilitychange and keydown listeners plus `setInterval`s that are never torn down. Elementor re-renders a widget on every control change, so listeners and timers stack up in the editor | `theme.js` | 5 |
| H | `letter-spacing: 0.02em` on Arabic text. Physical `text-align: right/left`, `left:`/`right:` in places. 800/900 weights used everywhere | `site.css` | 6, 7 |
| I | Admin and control strings are hardcoded Arabic with no `__()` (`'نعم'`, `'لا'`, category title, lightbox JS strings), written in **Egyptian colloquial** ("لو حذفت الصورة يرجع القسم…") | widgets, `theme.js` | 1 |
| J | Seed is idempotent only through a global option flag. Locations, contacts and projects are inserted with no existence check. Uses `get_page_by_title()` (deprecated since WP 6.2). Writes meta with `update_post_meta` *and* `update_field` by name | `inc/seed-once.php` | 17, 20 |
| K | One 51 KB unminified `site.css` and the full `theme.js` load on every page. `theme.json` present on a classic theme, with no dequeue of block-library or global-styles CSS | `inc/enqueue.php`, `theme.json` | 19 |
| L | Theme breakpoint `48rem` (768) vs Elementor mobile `≤767px`, so editor responsive values and theme media queries disagree at the boundary | `tokens.css` | 4 |
| M | Contact throttle keyed on `REMOTE_ADDR` (wrong behind Cloudflare or a proxy). Nonce hard-fails, which breaks when the page is cached | `inc/contact-form.php` | 13 |
| N | Widget control defaults run DB queries (`almasa_page_url`, `almasa_page_thumb_id`) inside `register_controls()`. Elementor builds control stacks on front-end renders too, so those queries run for every visitor | `class-almasa-widget.php`, `widgets-sections.php` | 2, 19 |
| O | Locations rendered as Google Maps **iframes**, one per location, on page load | `template-parts/sections/locations.php` | 19 |

**Keep from Al-Masa:** one markup source (widget → `get_template_part`). The `almasa_arg()` absent/empty semantics. `has_widget_inner_wrapper(): false`. `is_dynamic_content(): true`. Form with nonce, honeypot, CPT inbox and no-JS fallback. Arabic-Indic → ASCII digit normalization. `novalidate` with custom Arabic messages. `aria-live` status. Skip link. `dir="ltr"` on phone and email inputs.

---

## Critical Pitfalls

### Pitfall 1: Copying Egypt-specific (and Arabic-hardcoded) code from Al-Masa

**What goes wrong:**
Saudi numbers fail validation, and `tel:` links dial `+20…`. For example, `almasa_tel_href('0573825004')` → `tel:+20573825004`. Placeholders and examples show Egyptian formats. Admin help text reads as Egyptian dialect to a Saudi client. Strings can't be translated for the planned English version.

**Why it happens:**
"Reuse the proven architecture" turns into copy-paste of whole modules (`contact-form.php`, `helpers.php`). Locale-specific constants hide inside generic-looking helpers.

**How to avoid:**
- Treat every Al-Masa file as a **reference to rewrite**, not a file to copy. Grep the new theme for `+2`, `0020`, `01[0125]`, `01XXXXXXXXX`, `almasa` before P2 closes.
- One phone module, `inc/phone.php`, used by validation, `tel:`, `wa.me`, schema and display:
  - Normalize: Arabic-Indic/Persian digits → ASCII, then strip spaces, `-`, `(`, `)`, `.`.
  - Saudi mobile: `^(?:\+?966|00966)?0?(5\d{8})$` → store E.164 `+9665XXXXXXXX`. Don't whitelist carrier prefixes (050/053/054/055/056/057/058/059…); they change.
  - Saudi landline (B2B leads often give an office line): `^(?:\+?966|00966|0)(1[1-7]\d{7})$` → `+9661XXXXXXXX`.
  - Optionally accept toll-free `800XXXXXXX` and unified `9200XXXXX`. Mark this as client-confirmed scope.
  - Allow international `^\+\d{10,15}$` for foreign suppliers.
- Arabic error copy with a Saudi example: «رقم الجوال غير صحيح. مثال: 05XXXXXXXX». Placeholder `05XXXXXXXX`.
- Admin and editor microcopy in **Modern Standard Arabic**, never Egyptian colloquial (no «لو»، «يرجع»، «عشان»).
- Wrap **every** string in `__()`/`esc_html__()` with text domain `mwm`, including Elementor control labels, `label_on/off` and category titles. JS strings go through `wp_localize_script`/`wp_set_script_translations`. Decide msgid language in P1. Recommended: Arabic msgids (no build step can fail and leak English to Saudi visitors), with `en_US` translations added in v2.

**Warning signs:** `tel:+20` in rendered HTML. The contact form rejects `0573825004`. Search finds Arabic literals not inside `__()`.

**Phase to address:** P1 (i18n rule, phone module skeleton), P2 (form validation), P6 (grep audit).

---

### Pitfall 2: Brittle identifiers (page titles, slugs, theme slug) as lookup keys

**What goes wrong:**
The client renames «تواصل معنا» to «اتصل بنا». Every "contact" button now points at the home page (Al-Masa falls back to `home_url('/')`), and section images disappear. The client never sees an error. Customizer settings vanish if the theme folder or slug changes, because `theme_mods_{slug}` is per theme.

**Why it happens:**
Title lookups are convenient during seeding. Al-Masa used them in about 15 places.

**How to avoid:**
- Store page relationships as **IDs** in the global settings screen: "Contact page", "Quote page", "Services page". Use `wp_dropdown_pages` selects, seeded once.
- Widget link controls: the empty value falls back to the **settings page ID → permalink**, never a title.
- Store global settings in the **options API** (`mwm_settings`), not `theme_mods`, so they survive a theme rename or switch.
- Never run DB queries inside `register_controls()` for front-end requests. Compute dynamic defaults lazily in `render()`/template args, or cache per request.

**Warning signs:** `WP_Query( [ 'title' => …` or `get_page_by_title` anywhere in the theme. Arabic page titles appearing as PHP string literals.

**Phase to address:** P2 (settings model), P3 (widget link fallbacks).

---

### Pitfall 3: Unstable Elementor widget names, control IDs and defaults lose or rewrite content

**What goes wrong:**
- Renaming `get_name()` (`mwm-hero` → `mwm-home-hero`) leaves saved pages pointing at a widget that no longer exists, and the section disappears.
- Renaming a control ID (`title` → `heading`) orphans the saved value and the widget silently reverts to its default.
- Elementor saves only values that differ from defaults (verified: globals are stored as `__globals__` references). Changing a control `default` in code therefore **changes every page that never touched it**, possibly reintroducing copy the client deliberately left alone.
- Changing a control type (TEXT → WYSIWYG) or a repeater's field names corrupts stored values.

**Why it happens:**
Widget and control names feel like internal code, but they're a **persisted data schema** in `_elementor_data`.

**How to avoid:**
- Freeze widget names (`mwm-` prefix) and control IDs **before P4 content entry**. Document them as a schema in `PROJECT_RULES.md`.
- When a rename is unavoidable, read old keys as fallbacks in `template_args()`, or write a one-off migration over `_elementor_data`, then regenerate CSS.
- Defaults are for first-insert copy only. Changing them after P4 is a content change and needs a review.
- Keep the Al-Masa semantics: absent key = template default, present-but-empty = hidden, plus an explicit "إظهار" switcher for dynamic fields.

**Warning signs:** A diff touching `get_name()` or `add_control( '…'` IDs after pages exist. "The text I wrote disappeared" reports.

**Phase to address:** P3 (schema freeze at end of phase). P4 must not start content entry before the freeze.

---

### Pitfall 4: Elementor global settings fighting the theme design system

**What goes wrong:**
- **Kit typography and colors override the theme.** Kit rules target `.elementor-kit-{id}` on `<body>`, which out-specifies the theme's `body {}`. Fresh installs default to Roboto/Roboto Slab and Elementor's blue/green palette.
- **Google Fonts auto-load** (Roboto and any family a client picks in a typography control), straight from Google's CDN. That's a performance and privacy hit.
- **Elementor 4.0 Atomic editor is ON by default for new installs** (since April 2026, per Elementor's v4 FAQ). The panel leads with Atomic Elements, Variables and Classes. That's a second design system next to v3 Global Colors, which custom v3 widgets use. The client gets two places to change "the green".
- **Optimized Markup** (`e_optimized_markup`, on by default for new installs since 3.30) removes `.elementor-widget-container`. CSS written against it breaks.
- **Inline Font Icons** (`e_font_icon_svg`) stops loading Font Awesome/eicons on the front end. Hardcoded `<i class="fa…">` looks fine in the editor and **breaks on the live site**.
- **Breakpoint mismatch:** Elementor mobile is `≤767`, tablet `≤1024`. Theme queries at `768`/`1024` disagree at the boundaries (Al-Masa defect L).
- **Kit container defaults:** boxed width 1140px, container padding and gap. These add unexpected gutters around full-bleed section widgets and can cause horizontal overflow on 360px screens. MEDIUM confidence; verify on install.

**Why it happens:**
Elementor ships opinionated site-wide defaults, and nobody turns them off.

**How to avoid (P1 baseline, scripted in a one-time setup routine, documented):**
- `update_option( 'elementor_disable_color_schemes', 'yes' )` and `elementor_disable_typography_schemes = 'yes'`.
- Elementor → Settings → Advanced → **Google Fonts: Disable**, plus `add_filter( 'elementor/frontend/print_google_fonts', '__return_false' )` as a belt-and-braces measure. Register the self-hosted font in the font control via `elementor/fonts/additional_fonts` so editors can pick only brand fonts.
- Disable "Load Font Awesome 4 Support". Keep Inline Font Icons ON. Custom widgets render icons only via `\Elementor\Icons_Manager::render_icon()` or inline theme SVG.
- **Decide on Atomic Editor in P1.** Recommendation: disable it for v1 (Elementor → Settings → Atomic Editor) so the client works in one model: custom v3 widgets plus Kit globals. Revisit when v4 has a migration story. Elementor states legacy widgets remain supported. MEDIUM confidence on long-term support.
- Seed the Kit with exactly the brand tokens (system + custom colors, 2–4 typography presets) so Kit and `tokens.css` match. Theme CSS consumes `var(--e-global-color-…)` with a token fallback, giving one source of truth.
- Theme media queries use Elementor's breakpoints: mobile `max-width: 767px`, tablet `max-width: 1024px`. Don't add six custom Elementor breakpoints to mirror the QA list; each one multiplies the generated CSS.
- Kit: content width = token container width. Section widgets sit in full-width containers with padding 0.

**Warning signs:** Roboto requests in DevTools Network. `fonts.googleapis.com` in the page source. Two color systems in the editor. Body text in the wrong font only on Elementor pages.

**Phase to address:** P1 (settings baseline), P3 (icon rendering and globals in controls).

---

### Pitfall 5: Editor preview breaks (JS lifecycle, missing defaults, PHP-only data)

**What goes wrong:**
- Sliders, reveals and filters don't run in the editor, so the client sees invisible sections (`opacity:0` reveal) or stacked slides.
- Every control change re-renders the widget. Without teardown, timers and listeners multiply (Al-Masa defect G). Autoplay speeds up, the editor gets sluggish, memory climbs.
- Widgets that need CPT data render empty on a fresh install (no services yet), so the client thinks the widget is broken.
- Header and footer saved templates rendered through `get_builder_content_for_display()` can enqueue their post CSS late, in the body. Result: an unstyled header flash or CLS. MEDIUM confidence.

**How to avoid:**
- One idempotent `init(scope)` per component, bound via `elementorFrontend.hooks.addAction('frontend/element_ready/mwm-*.default', …)`. Give each instance an `AbortController`; pass `{signal}` to all listeners and abort on re-init or when `!el.isConnected`. Timers check `isConnected` on every tick.
- Under `.elementor-editor-active`, force reveal targets visible and autoplay off.
- Every widget has an **empty state** in the editor only: "لا توجد خدمات بعد — أضفها من لوحة التحكم ←" with an edit link. Nothing renders on the front end.
- Enqueue header/footer template CSS early: `\Elementor\Core\Files\CSS\Post::create( $id )->enqueue()` on `wp_enqueue_scripts`.

**Warning signs:** Open the editor, change a slider control 10 times, and autoplay is visibly faster. Check `getEventListeners(window)` in DevTools.

**Phase to address:** P3.

---

### Pitfall 6: "RTL" implemented as flipped LTR (physical CSS, hardcoded mirrored icons, broken bidi)

**What goes wrong:**
- Physical properties (`margin-left`, `left:`, `text-align:right`, `border-top-left-radius`, `translateX(-20px)`, `background-position: left`, gradients `to right`) are hand-tuned for RTL and break when English is added. Elementor's own `-rtl` CSS also flips some of them, causing double flips.
- Directional icons drawn pre-mirrored (Al-Masa defect F). Non-directional icons (phone, WhatsApp, check, play) wrongly flipped.
- **Off-canvas nav hidden at `left:-100%` in RTL causes horizontal page scroll.** In RTL the left side is the scrollable *end* side; LTR developers' instincts are inverted.
- `overflow-x: hidden` on `body` to "fix" that breaks `position: sticky` headers.
- **Bidi:** `+966 57 382 5004` inside an Arabic sentence renders as `57 382 5004 966+` (the plus jumps). Emails and Latin terms ("Bag Filters", "Duct system", "Solenoid Valve") with adjacent parentheses or periods get misplaced.
- Scroll-snap sliders: modern browsers report **negative `scrollLeft`** in RTL. Old code assuming positive values breaks prev/next and progress. Arrow keys and swipe direction must follow reading direction.
- Hidden-scrollbar tracks are not keyboard-scrollable (axe rule `scrollable-region-focusable`).

**How to avoid:**
- Lint rule: logical properties only (`margin-inline-start`, `inset-inline-end`, `padding-block`, `text-align: start`, `border-start-start-radius`). Use Stylelint `liberty/use-logical-spec` or a simple grep gate. For transforms, use `--dir: 1` (`[dir=rtl]{--dir:-1}`) and `translateX(calc(var(--dir) * 20px))`.
- Icons: draw in LTR. Add `.mwm-icon--dir` with `[dir=rtl] .mwm-icon--dir{transform:scaleX(-1)}`. Never flip phone, WhatsApp, check, clock or logos.
- Prev/next semantics follow reading direction. Read `getComputedStyle(track).direction` for `scrollTo` sign, key mapping and swipe mapping. Progress fill uses `transform-origin: inline-start` logic. Track gets `tabindex="0"`, `role="region"`, `aria-label`.
- Use `overflow-x: clip` on the page wrapper, not `hidden`.
- Bidi: every phone, email, URL, license number and Latin product term is output through a helper that wraps it in `<bdi dir="ltr">` or `<span dir="ltr" lang="en">` (`lang` makes screen readers switch voice). In plain-text contexts (`<title>`, meta, `wa.me` text) add U+200F/U+200E marks where needed.
- In Elementor CHOOSE alignment controls, use `start|center|end` values, not `left|right`.

**Warning signs:** A grep finds `-left`/`-right`/`left:`/`right:` in theme CSS. At 360px the page wobbles sideways. Phone numbers show a trailing `+`.

**Phase to address:** P1 (CSS rules and helpers), P3 (sliders, icons, controls), P5/P6 (RTL overflow QA at 360–430px).

---

### Pitfall 7: Arabic typography damage (faux bold, letter-spacing, clipped marks, numerals)

**What goes wrong:**
- **Faux bold/italic:** if only 400/700 files are self-hosted but CSS or Elementor typography asks for 800/900 (Al-Masa uses 800/900 heavily), the browser synthesizes smeared bold that ruins Arabic joins. Same for synthesized italic on Arabic.
- **Letter-spacing ≠ 0** breaks or gaps cursive joining (Al-Masa defect H). Elementor typography controls expose letter-spacing to the client.
- **Tight line-height** (Latin-style 1.2 body) clips Arabic ascenders, descenders and diacritics. It's worst with `-webkit-line-clamp` and `overflow:hidden` cards.
- `text-transform: uppercase` and `font-variant: small-caps` kickers are meaningless for Arabic and look broken on mixed Latin terms.
- **Numerals:** mixing Arabic-Indic (٠١٢) and Western (0123) digits across the site. Phone numbers in Arabic-Indic can't be copied into a dialer reliably.
- The chosen web font lacks glyphs for Arabic punctuation (، ؛ ؟) or Latin characters used in product names.

**How to avoid:**
- `font-synthesis: none` globally. Ship real files for every weight you use, max 3 (e.g. 400/500/700). Elementor typography weight options are limited to the shipped weights (via `additional_fonts` registration plus documentation).
- `letter-spacing: 0` on Arabic text. In widget typography group controls, exclude `letter_spacing`/`word_spacing` from the exposed fields (`'fields_options'` → hide), or document it as "Latin only".
- Line-height tokens: body 1.7–1.8, headings 1.3–1.4, never below 1.25. Line-clamped cards get `line-height ≥ 1.6` and `padding-block` slack.
- **Western digits site-wide** (dominant in Saudi corporate and government web copy, and copy-pasteable). Forms still *accept* Arabic-Indic and normalize them.
- Verify glyph coverage for the actual copy (، ؛ ؟ «» ـ plus Latin) before finalizing the font.

**Warning signs:** Bold headings look blurry. Gaps appear between Arabic letters. Dots under ي/ب are cut off in cards.

**Phase to address:** P1 (tokens, @font-face), P3 (typography control limits), P5 (visual check at 360px).

---

### Pitfall 8: Arabic web-font payload and loading strategy

**What goes wrong:**
Arabic fonts are large: full Arabic + Latin families run 100–300 KB per weight. Loading several static weights from Google's CDN (Al-Masa defect C) adds third-party DNS/TLS round-trips, blocks rendering via the CSS `@import`/link, and a wide Arabic font swapping in for a narrow system fallback re-wraps every line (CLS). Third-party font CDN calls also send visitor IPs abroad, a privacy concern under PDPL.

**How to avoid:**
- Self-host WOFF2 files in the theme (`assets/fonts/`), split by `unicode-range`: Arabic file `U+0600-06FF, U+0750-077F, U+0870-08FF, U+FB50-FDFF, U+FE70-FEFF, U+200C-200E`, plus a separate Latin file under the same family name.
- Prefer a **variable** Arabic font (one file covers the weight range) if the chosen family offers one, or cap at 2–3 static weights.
- No Python on this machine, so `pyftsubset` isn't available. Use pre-split files from **Fontsource** npm packages (already subset per script with `unicode-range`) or Node `subset-font` (harfbuzz) with layout features kept. **Never** subset Arabic with tools that drop GSUB/GPOS, or letters stop joining.
- `font-display: swap` on every face. `<link rel="preload" as="font" type="font/woff2" crossorigin>` for **one** file only (Arabic regular/variable). Add a metric-adjusted fallback face (`size-adjust`, `ascent-override`, `descent-override` over `local("Segoe UI"), local("Tahoma"), local("Arial")`) to reduce swap CLS.
- Budget: total font bytes on first view ≤ ~150 KB.

**Warning signs:** `fonts.gstatic.com` in Network. More than 3 font files on first view. CLS > 0.1 caused by text blocks.

**Phase to address:** P1 (font pipeline), P5 (measure and tune fallback metrics).

---

### Pitfall 9: Sabotaging hero LCP

**What goes wrong:**
- Hero `<h1>` and CTA start at `opacity:0` (reveal animation), so LCP waits for JS plus the 900ms transition (Al-Masa defect D).
- Hero image set as an Elementor/CSS **background-image**: invisible to the preload scanner, no `srcset`, and Elementor's "lazy load background images" can delay it further.
- Hero `<img>` gets `loading="lazy"` from WP's auto heuristics or a lazy-load plugin. Or WP gives `fetchpriority="high"` to the header **logo** instead of the hero, so two high-priority images compete.
- `size: 'full'` with `sizes="100vw"` serves the 2560px original to a 390px phone. A crossfade slideshow puts 5–8 full-size images in the DOM.
- Elementor **entrance animations** (Advanced → Motion Effects) on above-the-fold sections add `elementor-invisible`, so content stays hidden until JS runs.

**How to avoid:**
- Above-the-fold content is never hidden by reveal: no `data-reveal` on the first section. Animate only `transform` from a visible state, or skip motion in the hero.
- Hero = real `<img>` via `wp_get_attachment_image( $id, 'mwm-hero', … [ 'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async', 'sizes' => '100vw' ] )`. Register sizes 768/1280/1920 so `srcset` works. Slides 2+ use `loading="lazy"` and `fetchpriority="low"`, or, better, inject slides 2+ after `load`.
- Header logo: `loading="eager"`, `fetchpriority="auto"`, explicit width and height.
- Recommend a **static hero image** (single LCP) with an optional, pausable slideshow. Default to the static one.
- Documentation and a widget note: "لا تستخدم حركات الظهور (Entrance Animation) في القسم الأول".

**Warning signs:** Lighthouse "LCP element: h1" with large "render delay". "Largest Contentful Paint image was lazily loaded". LCP > 2.5s on mobile.

**Phase to address:** P3 (hero widget), P5 (verification).

---

### Pitfall 10: Brand colors that fail contrast, and client controls that reintroduce failures

**What goes wrong:**
`#5BBF75` (leaf green) and `#A5D7FC` (sky blue) are the most "on-brand" accents. Designers put them on white for text, links, icons, button fills with white labels, and focus rings. They fail even the **3:1 non-text** threshold (WCAG 1.4.11), not just text contrast.

**Calculated ratios (WCAG relative luminance):**

| Foreground on background | Ratio | Verdict |
|---|---|---|
| `#5BBF75` on `#FFFFFF` | **2.29** | Fails text **and** UI/graphics (3:1) |
| `#FFFFFF` on `#5BBF75` (white label on leaf-green button) | **2.29** | Fails |
| `#A5D7FC` on `#FFFFFF` | **1.53** | Fails everything; decorative only |
| `#28864B` on `#FFFFFF` | 4.56 | Passes AA text, barely. Any lighter hover/opacity fails |
| `#FFFFFF` on `#28864B` | 4.56 | Passes AA (keep exact hex on buttons) |
| `#28864B` on `#EAEAEA` (off-white sections) | **3.79** | **Fails** normal text. OK for large text (≥24px / 18.66px bold) and UI |
| `#A5D7FC` on `#28864B` | **2.98** | Fails |
| `#5BBF75` on `#28864B` | **1.99** | Fails |
| `#5BBF75` on `#194223` | 4.96 | Passes AA text |
| `#194223` on `#5BBF75` (deep-green label on leaf button) | 4.96 | Passes AA |
| `#A5D7FC` on `#194223` | 7.44 | Passes AAA |
| `#194223` on `#FFFFFF` | 11.4 | AAA |
| `#3A423C` on `#EAEAEA` | 8.6 | AAA |
| `#1A2BC3` on `#FFFFFF` | 9.6 | AAA |

**How to avoid:**
- Token roles, not just hexes: `--mwm-text` = `#194223` or `#3A423C`; `--mwm-link` = `#28864B` (white backgrounds only) or `#194223`; `--mwm-accent-decor` = `#5BBF75` (shapes, leaf pattern, large decorative only); `--mwm-on-dark-accent` = `#5BBF75` / `#A5D7FC` on `#194223`.
- Leaf-green buttons use **deep-green labels** (4.96). White labels go on `#28864B` or `#194223` only. Hover states darken; never lighten `#28864B`.
- Focus ring: `#194223` (or `#1A2BC3`) on light sections, `#A5D7FC` on deep-green sections, 2px plus offset.
- Primary-green body text on `#EAEAEA` sections is banned. Use `#194223`.
- Widget color controls default to **Kit globals** (`'global' => [ 'default' => Global_Colors::COLOR_… ]`) so client edits stay within the palette. The handoff guide includes a "safe pairs" table.

**Warning signs:** axe/Lighthouse "color-contrast" failures. Light-green links on white in mockups.

**Phase to address:** P1 (token roles), P3 (control defaults), P5 (axe pass), P6 (client guide).

---

### Pitfall 11: Motion and floating UI that violate WCAG 2.2 (2.2.2, 2.4.11, 2.5.8)

**What goes wrong:**
- **2.2.2 Pause, Stop, Hide:** the auto-advancing hero, infinite marquee and autoplay sliders have no visible pause control. Pausing on hover or focus is not enough; touch and screen-reader users can't hover (Al-Masa defect E).
- **2.4.11 Focus Not Obscured (Minimum):** the sticky header and the floating WhatsApp/call button cover the focused field or link when tabbing, especially on mobile.
- **2.5.8 Target Size (Minimum, 24×24 CSS px):** footer social icons, slider dots, the "×" close button.
- Lightbox and WhatsApp-chooser dialogs without a focus trap, Escape handling or focus return. Background still scrollable.

**How to avoid:**
- Any auto-moving content gets a visible, labelled pause/play button (`aria-pressed`). It also stops under `prefers-reduced-motion` and in the editor. Default autoplay **off** for sliders; the hero is static by default (see Pitfall 9). Marquee: pause button plus `animation-play-state` toggle, or don't ship a marquee.
- `html { scroll-padding-top: var(--mwm-header-h) }`. Floating buttons sit `inset-inline-end`/`bottom` with `env(safe-area-inset-bottom)` and enough `scroll-padding-bottom` that focused form fields aren't hidden. Hide the floating cluster while the quote form has focus on mobile.
- Minimum 44×44 for primary CTAs and floating buttons, never below 24×24 for anything.
- Use native `<dialog>` (`showModal()`) for the lightbox and the WhatsApp number chooser. That gives focus trap, Escape, inert background and focus return. Label it in Arabic.

**Warning signs:** Tab through the quote form on a 390px emulation and the WhatsApp bubble covers the submit button. No pause control is visible on the homepage.

**Phase to address:** P3 (components), P5 (keyboard and axe audit).

---

### Pitfall 12: Content integrity: invented, overstated or unlicensed content

**What goes wrong:**
- **Licence overclaim:** NCEC licensing covers exactly **two** fields (remediation and rehabilitation plans; pollution-control filters). A sitewide "مرخصون من المركز الوطني…" badge next to **ventilation systems** implies a licence that doesn't exist.
- Filler stats and counters ("+15 سنة خبرة", "+500 مشروع"), client logos, testimonials and project names. All explicitly out of scope, and a credibility and legal risk.
- **Government and initiative logos:** the NCEC logo, Vision 2030 logo or Saudi Green Initiative logo used decoratively. Use of these marks is restricted. Show the client's licence certificate image or number instead, once provided. MEDIUM confidence on the specific policies; confirm with the client.
- **Legal entity wording:** the services PDF calls the business «المؤسسة» (establishment), but the provided About copy says «شركة». In KSA, مؤسسة vs شركة reflects the commercial-registration type. Mislabeling misrepresents the entity. **Confirm against the CR** before writing copy or schema `legalName`.
- "Sonolite Valve" typo: almost certainly *Solenoid Valve*. Publishing either version unconfirmed is wrong.
- **Photos:** PDF company profiles in this sector often include stock or internet photos, or images showing other companies' branded equipment. Presenting them as "our projects" is misleading and a licensing risk. Page screenshots (with baked-in text, frames and JPEG artifacts) used instead of extracted originals. Low-res images upscaled for full-bleed heroes.
- Arabic copy machine-translated, inconsistent in register, or in a non-Saudi dialect.

**How to avoid:**
- A content ledger (`docs/content-sources.md`): every claim maps to a source line in PROJECT.md or the PDF. Licence badges are per service line via a CPT field (`licensed_by_ncec` true/false), never global.
- A client confirmation checklist before P4 seeding closes: entity type (مؤسسة/شركة), the Solenoid spelling, which PDF photos are their own work, licence numbers and images, branch addresses.
- Extract embedded images from the PDF at native resolution (Node `pdfjs-dist` operator-list image extraction, or `pdfimages` if poppler is installed). Record pixel dimensions. Assign images under 1600px wide to cards and galleries only, never the hero. No AI upscaling presented as real photos.
- Reverse-image-search hero and gallery candidates. Exclude anything that turns up as stock or a third party's photo.
- Copy in Modern Standard Arabic, professional Saudi B2B register. Have a native reviewer read it.

**Warning signs:** Any number in copy that isn't in PROJECT.md. A licence badge on the ventilation page. Hero images that look soft at 1440px.

**Phase to address:** P4 (seeding and content ledger), P6 (client sign-off).

---

### Pitfall 13: Leads silently lost (mail delivery, caching, spam controls)

**What goes wrong:**
- `wp_mail()` "works" (returns true or fails silently) on XAMPP, which has no MTA, so devs never see the notification path fail. On shared hosting, PHP `mail()` from `wordpress@domain` to **Gmail** (`mwmksa92@gmail.com`) is spam-foldered or rejected without SPF/DKIM alignment. Gmail's sender requirements enforce authentication.
- `From:` set to the visitor's email fails DMARC, so the message is rejected.
- Full-page caching (LiteSpeed Cache, common on Saudi/Gulf hosts, or Cloudflare APO) serves a **stale nonce** after 12–24h, and every submission fails with "انتهت صلاحية الصفحة".
- Throttle by `REMOTE_ADDR` behind Cloudflare or a proxy: all visitors share one IP and block each other, or the throttle does nothing.
- "Optimize/Delay JS" options in cache plugins break the form's AJAX submit and the WhatsApp chooser.
- reCAPTCHA v3 added "for safety" costs ~400 KB+ of third-party JS on every page and transfers data abroad.

**How to avoid:**
- The **dashboard inbox (CPT) is the source of truth**; email is a notification only (Al-Masa pattern, keep it). Show "email failed" status per request via the `wp_mail_failed` hook.
- Production: an SMTP plugin (FluentSMTP or WP Mail SMTP) with a domain mailbox. `From:` = domain address, `Reply-To:` = visitor. Configure SPF and DKIM on the domain. Send a test to the Gmail recipient during P6.
- Notification recipient = a settings field ("بريد استقبال الطلبات"), not `admin_email`.
- Nonce: either fetch a fresh nonce via an uncached AJAX call on form focus, or for anonymous submissions rely on honeypot + minimum fill time (e.g. ≥3s) + per-IP rate limit + server validation rather than hard-failing on a cached nonce. Exclude form pages from page cache if the host allows.
- Client IP: trust `CF-Connecting-IP`/`X-Forwarded-For` only when behind a known proxy, configurable. Store only a salted hash in transients, never the raw IP in the request record.
- Escalate spam defenses only if spam appears: Cloudflare Turnstile (light) before reCAPTCHA.
- No-JS fallback via `admin-post.php` (keep).

**Warning signs:** No test email received at the Gmail address from staging. Form errors appear only on the live, cached site.

**Phase to address:** P2 (form backend design), P3 (form widget), P6 (production mail and cache test).

---

### Pitfall 14: PDPL (Saudi Personal Data Protection Law) gaps on the lead form

**What goes wrong:**
The form collects name, phone, email and company with no privacy notice, no stated purpose or retention, no way to request deletion, and requests kept forever. PDPL is enforced by SDAIA (the law took effect Sept 2023 with a one-year grace period). It requires the controller to inform data subjects **before or at collection** of: controller identity and contact, purpose and legal basis, retention period or criteria, rights and how to exercise them, how to withdraw consent, and which fields are mandatory or optional (Implementing Regulation, Art. 12 notice requirements; SDAIA Privacy Policy Guideline).

**How to avoid:**
- A «سياسة الخصوصية» page (seeded draft, client and legal review), linked from the footer **and directly above the submit button**, with a short notice: purpose (responding to the quote/visit request), retention, contact for rights requests.
- Mark required vs optional fields visibly. Collect the minimum: name, phone, service, city, message. Email optional.
- An optional, unticked acknowledgment checkbox («أوافق على سياسة الخصوصية») if the client's counsel prefers explicit consent. Record consent and timestamp with the request.
- Retention: a settings field (e.g. 24 months) plus a scheduled cleanup (WP-Cron). Register the request CPT with WordPress privacy **exporters and erasers** (`wp_privacy_personal_data_exporters/erasers`) so the client can satisfy access and deletion requests from Tools → Privacy.
- Avoid third-party trackers and embeds that transfer data abroad without disclosure: Google Fonts CDN, Maps iframes on load, reCAPTCHA. If analytics are added later, add a consent notice.
- Note to client (not legal advice): Gmail and non-KSA hosting mean cross-border transfer, which is covered by SDAIA's transfer regulation. Flag it for their counsel.

**Warning signs:** No privacy link near the form. The request CPT doesn't appear in Tools → Erase Personal Data.

**Phase to address:** P2 (data handling, erasers, retention), P4 (privacy page copy), P6 (client sign-off).

---

### Pitfall 15: Deploy migration breaks Elementor (hardcoded localhost URLs)

**What goes wrong:**
`http://localhost/mwmpower-ksa` lives in:
- `post_content`
- **`_elementor_data` as JSON-escaped `http:\/\/localhost\/mwmpower-ksa`**, which a plain search-replace misses
- Elementor-generated CSS files in `uploads/elementor/css/` (background image URLs)
- PHP-serialized options and theme_mods, where a raw SQL replace corrupts string lengths
- menu custom links and ACF link fields

Moving from a subdirectory to the domain root also breaks root-relative paths like `/mwmpower-ksa/wp-content/…`. Result: broken images, a blank Elementor editor, wrong fonts.

**How to avoid:**
- Never hardcode URLs in theme code: `get_theme_file_uri()`, `home_url()`. Fonts in CSS via relative `url('../fonts/…')`.
- Store attachment **IDs**, not URLs, in widget settings and meta (the Media control stores both; templates render from the ID).
- Deploy runbook (P6):
  1. `wp search-replace 'http://localhost/mwmpower-ksa' 'https://domain' --all-tables --precise` (serialization-safe), then a second pass for the JSON-escaped form `'http:\/\/localhost\/mwmpower-ksa'`, or Elementor → Tools → **Replace URL**, which handles `\/`.
  2. Replace the path variant `/mwmpower-ksa/` if moving to root.
  3. Elementor → Tools → **Regenerate CSS & Data**, then clear page cache.
  4. Check Settings → General WordPress and Site URL match. Don't rewrite `guid`.
- Test the runbook once on a staging copy before launch.

**Warning signs:** `localhost` in production page source or CSS. Mixed-content warnings.

**Phase to address:** P1 (no hardcoded URLs, ID-based media), P6 (runbook and execution).

---

### Pitfall 16: Windows/XAMPP environment traps

**What goes wrong:**
- **Case-insensitive filesystem:** `get_template_part('template-parts/sections/Hero')` or `require 'inc/Elementor/…'` works on Windows and **fatals on Linux production**. Uppercase `.JPG` extensions likewise.
- **UTF-8 BOM** in PHP files (some Windows editors add it) sends output before headers. Admin-AJAX JSON from the form becomes invalid and redirects fail ("headers already sent").
- **PowerShell redirection encoding:** `mysqldump … > dump.sql` in Windows PowerShell 5 writes **UTF-16**, which corrupts Arabic content and fails import. Same for generated PHP or JSON via `>`.
- **Line endings:** CRLF churn in diffs. Shell scripts with CRLF break on Linux.
- **Git not on PATH** (`C:\Program Files\Git\cmd`): automation silently skips commits.
- **Version drift:** XAMPP PHP 8.2/8.3 vs host PHP 7.4/8.0 (typed properties, `str_contains`, enums). MariaDB 10.4 (XAMPP) vs MySQL 8 (`utf8mb4_0900_ai_ci` collation can't be imported back into MariaDB).
- XAMPP lacks WebP/AVIF in GD/Imagick, so image sub-size format behavior differs from production.
- No Python, so Python tools (pyftsubset, fontTools) can't be used.

**How to avoid:**
- Convention: all theme paths and file names lowercase kebab-case. A P6 check script (Node) compares every `get_template_part`/`require` path to the real file case.
- `.editorconfig` (`charset = utf-8`, `end_of_line = lf`) and `.gitattributes` (`* text=auto eol=lf`, binaries marked) committed in P1. Don't touch global git config.
- DB exports: `mysqldump --result-file=dump.sql --default-character-set=utf8mb4`, or run through `cmd /c`, never PowerShell `>`. Confirm `DB_CHARSET = utf8mb4`.
- Invoke git by full path, or prepend PATH per session in scripts.
- Target PHP: ask the host's PHP and MySQL versions in P1, and code to the lower bound. If unknown, assume PHP 8.1 syntax.
- Node 24 is available. Use Node tooling (sharp for images, Fontsource/subset-font for fonts, pdfjs-dist for PDF images).

**Warning signs:** Arabic shows as `Ø§Ù„` mojibake after import. Form AJAX returns HTML or garbage. Production 500 errors on a template include.

**Phase to address:** P1 (repo hygiene, env facts), P4 (import scripts), P6 (deploy).

---

### Pitfall 17: ACF Free workarounds that backfire

**What goes wrong:**
- **Options page substitute via Customizer:** settings stored in `theme_mods_mwm` are lost on theme rename. Customizer is hard for clients to find and has no proper field types for multiple branches.
- **Singleton-post hack** (a hidden "Settings" post with an ACF group): the client trashes it, or its ID differs between local and production, and every site-wide phone number disappears.
- **Pseudo-repeaters** (`phone_1…phone_5`, `branch_1_city…`) are rigid, half-empty and awkward in templates. Branches, licences and services belong in **CPTs**.
- **Two sources of truth:** field groups registered in PHP *and* saved via UI to `acf-json/` (Al-Masa had both mechanisms present). Editing a group on production writes JSON into the theme folder on the server, which then diverges from git and is overwritten on the next theme deploy. "Sync available" notices confuse the client.
- **Unstable field keys/names:** renaming a field name orphans existing meta (like Elementor control IDs). Auto-generated keys differ between environments when groups are created by hand in each.
- **Return-format mismatch:** an Image field returning an array in one group and an ID in another, so templates break.
- **Seeding with `update_field('name', …)` before groups exist, or via plain `update_post_meta`,** leaves out the `_name → field_key` reference, so `get_field()` can't format values (Al-Masa defect J).
- **ACF 6.2.5+ escaping** of `the_field()`/shortcode output strips HTML and shows admin notices.
- ACF deactivated means `get_field()` fatals the front end.

**How to avoid:**
- Global settings = **custom admin page using the Settings API** (`register_setting` with sanitize callbacks), stored in one option `mwm_settings`. That gives the "one global settings screen" with no Pro dependency.
- Repeatable data = CPTs: `mwm_service` (with parent/child for sub-services), `mwm_branch`, `mwm_license`, `mwm_project`/gallery, `mwm_request`.
- Gallery = custom `wp.media` multi-select metabox storing an ordered attachment-ID array (Al-Masa `project-gallery.php` pattern). Use the same meta key an ACF Gallery would use, if Pro is ever added.
- ACF field groups **registered in PHP only** (`acf_add_local_field_group`) with hand-written stable keys (`field_mwm_branch_phone`). No `acf-json` folder, or a read-only JSON load path with no save path. That gives one source of truth, versioned in git and not editable on production.
- Standardize `return_format = 'id'` for images and files. All reads through `mwm_field( $name, $post_id )`, which falls back to `get_post_meta` when ACF is inactive. Escape at output.
- Seeds call `update_field( 'field_mwm_…', $value, $id )` **by key**, after `acf/init`.

**Warning signs:** ACF → Field Groups shows "Sync available". The settings screen is empty after deploy. `get_field()` returns raw IDs in one place and arrays elsewhere.

**Phase to address:** P2.

---

### Pitfall 18: Local SEO and multi-branch schema mistakes

**What goes wrong:**
- One `LocalBusiness` with three `address` values, or three branches sharing one phone, invented `geo`, invented `openingHours`, invented street addresses. Addresses aren't provided yet, so any address-level schema now would be fabricated.
- **Doorway pages:** "خدمات بيئية في جازان / الرياض / الدمام" city pages with spun, near-identical copy.
- **NAP drift:** the name appears as «MWM للمعالجة البيئية والحلول المستدامة», «ام دبليو ام», «MWM KSA» across the site, Google Business Profile and directories. Phones appear as 057…, +966 57…, 966-57-… inconsistently.
- **Arabic slugs** turn into `%D8%AE%D8%AF…` when shared on WhatsApp or email, in schema `@id`s and in analytics. That looks spammy and breaks in some apps. Title-based code lookups also tie into slugs.
- WP core sitemap exposes `wp-sitemap-users-1.xml` (admin username) and includes internal CPTs (requests, licences, branches) if they're public.
- Theme outputs OG and JSON-LD, then the client installs Yoast or Rank Math later, giving duplicate `og:*` and conflicting schema graphs.
- Multiple `<h1>`: every section widget offers "H1" in its heading-tag control.

**How to avoid:**
- Schema graph: `Organization` (sitewide: canonical `name`, `legalName` once the CR confirms it, `logo`, `telephone` E.164, `email`, `areaServed: SA`, `sameAs` only when filled). Each **branch with a filled address** gets its own `LocalBusiness` (or `ProfessionalService`) node with its own `address`, `telephone` and `parentOrganization`. Branches without an address emit **no** LocalBusiness node, only `areaServed` cities on the Organization. Each service page gets `Service` with `provider` → Organization and `areaServed`.
- One branches page with real data, not per-city doorway pages, until each branch has unique facts.
- A canonical NAP block in settings (Arabic name, English name, display phone format, E.164). Header, footer, contact page, schema and GBP-handoff notes all read from it.
- Recommendation: **ASCII slugs** for pages and CPT rewrite bases (`/services/dust-filters/`, `/gallery/`, `/branches/`, `/quote/`) with Arabic titles. Readable when shared, stable for code. MEDIUM; Google indexes Arabic slugs fine. This is mostly about robustness and shareability.
- Disable the users sitemap provider (`wp_sitemaps_add_provider` filter). Internal CPTs `public => false`. Disable author archives.
- Theme SEO output is guarded: skip OG/JSON-LD when `WPSEO_VERSION` or `RANK_MATH_VERSION` is defined.
- Heading-tag controls default to `h2`. Only the hero and page-hero default to `h1`. QA checks one `<h1>` per page.
- Validate with Google's Rich Results Test and the Schema.org validator in P5.

**Warning signs:** JSON-LD containing coordinates or hours not present in PROJECT.md. Percent-encoded URLs in the sitemap.

**Phase to address:** P2 (NAP and branch data model), P4 (slugs on seeding), P5 (schema output and validation).

---

### Pitfall 19: Front-end asset bloat that sinks Lighthouse mobile

**What goes wrong:**
- Classic theme with `theme.json` (Al-Masa) plus no dequeues means WP prints `global-styles-inline-css`, `wp-block-library`, `classic-theme-styles` (block CSS nobody uses).
- One monolithic theme CSS and JS on every page (Al-Masa defect K). Elementor frontend CSS and JS (with its dependencies) on top.
- Three Google Maps iframes (one per branch) on load: ~1 MB+ of third-party JS plus cookies (Al-Masa defect O).
- Deep DOM: section → container → `e-con-inner` → widget → inner wrappers, multiplied across 10 sections. Lighthouse flags excessive DOM size (≈1,400+ nodes).
- Floating WhatsApp via a chat-widget plugin (third-party JS) instead of a plain link.
- Testing Lighthouse on XAMPP, or while logged in (admin bar plus Elementor editor assets), gives meaningless numbers.

**How to avoid:**
- No `theme.json`, or a minimal one with block styles off. Dequeue `wp-block-library`, `wp-block-library-theme`, `global-styles`, `classic-theme-styles` on the front end when no block content is used.
- Small global CSS (tokens, base, layout, header, footer; aim ≤ 25 KB min). **Per-widget CSS/JS** registered with `wp_register_style/script` and declared via the widget's `get_style_depends()`/`get_script_depends()`, so Elementor loads them only where the widget is used. Vanilla JS with `defer`/`strategy => 'defer'`, no jQuery dependency in theme code.
- Maps: a **click-to-load facade** (static image or styled card plus «عرض على الخريطة» link to `https://www.google.com/maps/search/?api=1&query=…`). The iframe loads only on click, and only when the editor filled a location.
- One full-width container per section. Widgets don't nest needless wrappers. Keep `has_widget_inner_wrapper(): false`.
- Floating WhatsApp and call = plain `<a>`/`<button>` plus inline SVG, zero dependencies.
- Measure on a **staging host** with production caching, logged out, Lighthouse mobile preset, three runs median. Also PageSpeed Insights field data post-launch.
- Production cache plugin: exclude theme JS and the form from "delay JS" and "combine". Don't minify-combine Elementor files blindly.

**Warning signs:** More than ~15 requests or more than ~1,500 DOM nodes on the homepage. `wp-block-library` in source. TBT > 200ms.

**Phase to address:** P1 (enqueue architecture, dequeues), P3 (per-widget assets), P5 (measure and fix).

---

### Pitfall 20: Media import and image pipeline mistakes

**What goes wrong:**
- Files copied directly into `uploads/` and registered with `wp_insert_attachment()` keep spaces, Arabic characters and uppercase in their names (the root cause of Al-Masa's `%20` hack). They also skip `big_image_size_threshold` scaling.
- Seeds that aren't idempotent per record create duplicate attachments, services and branches on re-run (Al-Masa defect J).
- No alt text, or alt = file name ("page-07-img-2"). The same generic alt on every image.
- Uploading 6–10 MB originals. No WebP. XAMPP's GD can't make WebP sub-sizes, so locally generated sizes are JPEG/PNG only.

**How to avoid:**
- Import pipeline (Node + WP-CLI): extract, then rename to `mwm-{service}-{nn}.jpg` (ASCII kebab-case), resize the longest edge to ≤2560 and convert to high-quality WebP (sharp), then import via `wp media import` (or `media_handle_sideload`) so WP sanitizes names and generates sizes. Set the Arabic alt text and title from a manifest (`docs/media-manifest.json`: file → service, alt, source PDF page, own-photo confirmed y/n).
- Seeds are idempotent **per record**: look up by a stable `_mwm_seed_key` meta, not by title. Use `get_posts( [ 'meta_key' => '_mwm_seed_key', … ] )`. Never use `get_page_by_title()`.
- Alt text describes the scene in Arabic (e.g. «تركيب فلتر غبار لكسارة»), never invents a client or location.
- Hero and gallery sizes registered once (`mwm-hero` 1920, `mwm-card` 960×720, `mwm-thumb`) before import, so sub-sizes exist.

**Warning signs:** `%20` or `%D8` in image URLs. Duplicate media after a second seed run. Images over 500 KB served to mobile.

**Phase to address:** P4 (pipeline and seeding). Image sizes registered in P1.

---

## Technical Debt Patterns

| Shortcut | Immediate Benefit | Long-term Cost | When Acceptable |
|----------|-------------------|----------------|-----------------|
| Copy Al-Masa modules wholesale | Fast start | Egypt phone logic, title lookups, leaks and bloat carried over (Pitfalls 1, 2, 5, 19) | Never. Reimplement against the reference |
| Page/content lookup by Arabic title | No settings UI needed | Silent breakage when the client renames pages | Never |
| Settings in Customizer `theme_mods` | Live preview for free | Lost on theme rename; poor UX for multi-field data | Header/footer template pick only (keep Al-Masa) |
| Monolithic `site.css`/`theme.js` | One file to manage | Unused CSS/JS on every page; Lighthouse penalty | Only while P3 is in progress; split before P5 |
| Hardcoded Arabic strings without `__()` | Faster typing | No English later; inconsistent microcopy | Never |
| Physical CSS "because we're RTL only" | Familiar | Rewrite for English; Elementor RTL double-flips | Never in theme CSS |
| ACF groups edited via UI on production | Quick fix | JSON/git divergence; overwritten on deploy | Never. Change in code, deploy |
| Google Maps iframe per branch | Looks complete | ~1 MB+ third-party, privacy, LCP/TBT | Never on load; click-to-load only |
| Slideshow hero | "Premium" feel | LCP, 2.2.2 pause control, weight | Only if pausable, static first slide, lazy rest |
| Seeding content by direct SQL or raw meta | Fast | Missing ACF key refs, no sanitization, duplicates | Never. Use WP/ACF APIs with seed keys |

## Integration Gotchas

| Integration | Common Mistake | Correct Approach |
|-------------|----------------|------------------|
| **WhatsApp** | `wa.me/+966…`, `wa.me/0573…`, `whatsapp://send` (fails on desktop), unencoded Arabic `?text=` | `https://wa.me/966573825004?text=` + `rawurlencode( $arabic_text )` (`%0A` for newlines). Digits only, country code, no `+`/`00`/leading 0. Number chooser in a `<dialog>`; both numbers come from settings |
| **`tel:` links** | `tel:0573825004` (fails abroad), `tel:+20…` (Al-Masa helper), spaces inside href | `tel:+966573825004`. Display `057 382 5004` or `+966 57 382 5004` inside `<bdi dir="ltr">` |
| **`mailto:`** | Email shown without bidi isolation; Arabic subject unencoded | `mailto:` + `rawurlencode` subject; wrap the displayed address in `<bdi>` |
| **Gmail recipient** | PHP `mail()`, `From:` visitor address, no SPF/DKIM | SMTP plugin with a domain mailbox, `Reply-To:` visitor, SPF and DKIM on the domain, test from staging |
| **Google Maps** | Embeds on load; invented coordinates | Click-to-load facade; query from the editor-filled address or `map_query` only |
| **Google Business Profile** | Site NAP differs from GBP; branch pins without addresses | Canonical NAP from settings; client verifies each GBP listing matches exactly |
| **Elementor** | Hardcoded `<i class="fa">`, CSS on `.elementor-widget-container`, Kit defaults left on, Atomic editor confusion | `Icons_Manager::render_icon()`, own class hooks, Kit seeded from tokens, Atomic decision in P1 |
| **Elementor Element Caching** | Marking form/CPT widgets `is_dynamic_content(): false` caches the nonce and stale lists | Keep `true` (the default) for any widget with nonces, CPT queries or per-page data; `false` only for purely static widgets |
| **ACF** | `the_field()` with HTML (6.2.5+ escaping notices); ACF inactive means fatal | `get_field` via wrapper with meta fallback; escape at output |
| **SEO plugins (later)** | Duplicate OG/schema with the theme's own output | Theme output auto-disables when Yoast or Rank Math is detected |
| **Cache plugins (LiteSpeed etc.)** | Delay/combine JS breaks form and dialogs; cached nonce | Exclusions list in handoff doc; nonce strategy from Pitfall 13 |
| **Cloudflare/proxy** | Rate-limit on proxy IP | Trusted-proxy header setting |

## Performance Traps

| Trap | Symptoms | Prevention | When It Breaks |
|------|----------|------------|----------------|
| Full-size hero images, multiple slides in DOM | LCP > 3s mobile, MBs transferred | `srcset` sizes, single static LCP image, lazy slides 2+ | Immediately on mobile 4G |
| Arabic font weights from CDN | Render delay, CLS on swap | Self-host subset WOFF2, ≤3 weights or variable, 1 preload, metric fallback | Immediately |
| Reveal animations on above-the-fold | LCP "render delay" | No reveal in first viewport | Immediately |
| Gallery with all images in DOM | Heavy page as the gallery grows | Paginate/"load more" with server limit; lazy images; `mwm-card` size | Over ~40 images (the PDF alone has ~25) |
| Unbounded CPT queries in widgets (`posts_per_page => -1`) | Slow TTFB as content grows | Explicit limits, `no_found_rows`, request-scoped caching | Hundreds of records (gallery) |
| DB queries during control registration | Extra queries per front-end widget render | Lazy defaults in render; editor-only data notes | Every page view |
| Unread-count query on every admin page | Admin slowness as requests accumulate | `fields => ids` with a cap, or a cached counter updated on insert | Thousands of requests |
| Many Elementor custom breakpoints | Large per-page CSS | Keep default mobile/tablet (plus at most one extra) | Each added breakpoint |

## Security Mistakes

| Mistake | Risk | Prevention |
|---------|------|------------|
| Request CPT public or in REST | Lead PII exposed via `/wp-json/wp/v2/…` or sitemap | `public => false`, `show_in_rest => false`, `publicly_queryable => false`, `create_posts => do_not_allow` (Al-Masa pattern) |
| Storing raw visitor IP and user agent with leads | PDPL over-collection | Hash only in a short-lived transient for throttling |
| User enumeration (`/?author=1`, users sitemap, REST users) | Admin username leak, brute force | Disable author archives, users sitemap provider, restrict REST users endpoint |
| Unescaped widget settings (Elementor URL/text) | Stored XSS by any editor account | `esc_html`/`esc_attr`/`esc_url` in templates; `wp_kses_post` for WYSIWYG |
| Settings page without capability check or sanitize | Privilege misuse, broken output | `manage_options` (or a custom cap), `register_setting` sanitize callbacks; phone fields normalized |
| Form email header injection | Spam relay | Sanitize name/email; never put raw input in headers except a validated `Reply-To` |
| `WP_DEBUG_DISPLAY` on in production; debug.log web-readable | Path and info leak | Production `WP_DEBUG` false; protect `wp-content/debug.log` |
| Theme author link or credits on the public site | Explicitly forbidden by project rules | Author only in `style.css` headers |

## UX Pitfalls

| Pitfall | User Impact | Better Approach |
|---------|-------------|-----------------|
| WhatsApp float with two unexplained numbers | Visitor hesitates, picks randomly | Chooser labels from settings (e.g. «المبيعات»، «الدعم الفني» or branch names). If no label, show the number |
| Float covers CTAs, cookie bar or iOS bottom bar | Mis-taps, hidden submit | Safe-area insets, hide while the form is focused, `inset-inline-end` (bottom-left in RTL) |
| Quote form asks for too much | Drop-off on mobile | Minimal fields: name, phone, service (select from CPT), city, optional message/email; one column; big targets |
| Native HTML5 validation bubbles | English browser messages on an Arabic site | `novalidate` plus Arabic inline errors via `aria-describedby`, focus first invalid field (keep the Al-Masa approach) |
| Phone input aligned left in an RTL form | Looks broken | `dir="ltr"` with `text-align: right` on the input; placeholder `05XXXXXXXX` |
| Success message only in a toast | Screen-reader users miss it; mobile users don't notice | `role="status"`/`aria-live`, scroll into view, keep a "WhatsApp us now" fallback CTA |
| Empty optional data shows as empty labels ("السجل التجاري: ") | Looks unfinished | Hide-when-empty everywhere (project rule); editor-only placeholder notes |
| Licence section with no licence data yet | Unverifiable claim | Render text facts only; image and number slots appear once filled |
| Client overwhelmed by 80 controls per widget | Breaks the design, calls the developer | Group controls (المحتوى / التخطيط / الألوان / الخطوط), sane defaults, globals-first colors, short Arabic descriptions; deep controls behind "متقدم" sections |

## "Looks Done But Isn't" Checklist

- [ ] **RTL:** Often missing overflow checks. Verify there's no horizontal scroll at 360/375/390/430 with the menu open and closed, and that theme CSS contains no `left|right` physical properties.
- [ ] **Bidi:** Phone numbers, emails, licence numbers and Latin terms render correctly mid-sentence (no jumping `+`, parentheses in place).
- [ ] **Directional icons:** Verify the arrows point along reading direction and phone/WhatsApp icons are not mirrored.
- [ ] **Typography:** Check that no synthesized bold appears (DevTools → Rendered Fonts shows real faces), letter-spacing is 0 on Arabic, and diacritics aren't clipped in line-clamped cards.
- [ ] **Fonts:** Verify no requests go to `fonts.googleapis.com`/`gstatic.com`, at most 3 font files load on first view, and one preload is in place.
- [ ] **Elementor baseline:** Default colors/fonts disabled, Google Fonts disabled, FA4 shim off, Atomic decision applied, Kit = tokens, breakpoints aligned.
- [ ] **Editor:** Every MWM widget renders correctly in the editor on a **fresh DB** (empty-state notes), sliders work after 10 control changes without speeding up, and nothing is invisible due to reveal.
- [ ] **Schema freeze:** Widget names and control IDs documented, with no renames after P4.
- [ ] **Hero LCP:** LCP element is the hero `<img>` with `fetchpriority=high`, not lazy and not hidden by animation. LCP < 2.5s on mobile staging.
- [ ] **Contrast:** No `#5BBF75`/`#A5D7FC` text on light backgrounds, no `#28864B` body text on `#EAEAEA`, and focus rings meet 3:1.
- [ ] **Motion:** Each auto-moving element has a visible pause control. Reduced motion stops all of it.
- [ ] **Focus not obscured:** Tab through the quote form at 390px and confirm no focused element is hidden by the sticky header or floating buttons.
- [ ] **Form:** Accepts `0573825004`, `+966573825004`, `٠٥٧٣٨٢٥٠٠٤`, a landline `0112345678`, and rejects an Egyptian `01012345678` unless it's in `+20` international form.
- [ ] **Form delivery:** A submission on **production** appears in the inbox **and** arrives at the Gmail recipient (not spam), still works after the page has been cached for 24h, and the no-JS fallback works.
- [ ] **PDPL:** Privacy link above submit, required/optional marked, request CPT in WP privacy export/erase tools, retention cleanup scheduled.
- [ ] **Links:** WhatsApp `wa.me/9665…` opens with Arabic prefill on Android, iOS and desktop. `tel:` uses E.164.
- [ ] **Content ledger:** Every claim is traceable. No licence badge on ventilation. Entity type and Solenoid spelling confirmed by the client. Photo ownership confirmed.
- [ ] **SEO:** One `<h1>` per page, ASCII slugs, no users sitemap, internal CPTs absent from the sitemap, JSON-LD valid with no invented geo/hours, OG image set.
- [ ] **Deploy:** No `localhost` in production HTML, CSS or DB, Elementor CSS regenerated, file-case check passed.
- [ ] **Hide-when-empty:** Clear each optional settings field and confirm its UI disappears (CR, VAT, social, licence numbers, branch addresses, maps).

## Recovery Strategies

| Pitfall | Recovery Cost | Recovery Steps |
|---------|---------------|----------------|
| Widget/control renamed after content entry | MEDIUM | Restore the old ID as an alias in `template_args()`, or run a migration over `_elementor_data`, then Regenerate CSS; verify every page |
| Kit/Google Fonts left on, inconsistent styling | LOW | Apply the P1 settings, re-seed Kit globals, Regenerate CSS, purge cache |
| Localhost URLs on production | LOW–MEDIUM | `wp search-replace` (both plain and `\/`-escaped forms), Elementor Replace URL, Regenerate CSS, purge CDN |
| Mojibake Arabic after DB import | MEDIUM | Re-export with `--result-file` and utf8mb4 from the source; never "fix" in place |
| Duplicate seeded records | LOW | Delete by `_mwm_seed_key` duplicates; make the seed idempotent per record |
| Leads lost to mail failure | HIGH (lost business) | The inbox CPT prevents data loss. Fix SMTP, then email the client the backlog from the inbox |
| Contrast failures introduced by the client via color controls | LOW | Reset controls to globals; tighten control defaults; add safe-pairs guidance |
| Overclaimed licence/stat copy published | MEDIUM (reputation) | Remove immediately, re-audit against the content ledger, client sign-off |
| Case-sensitivity fatal on production | LOW | Rename files to lowercase, fix references, add the P6 check script to the pre-deploy routine |

## Pitfall-to-Phase Mapping

| Pitfall | Prevention Phase | Verification |
|---------|------------------|--------------|
| 1. Al-Masa Egypt/hardcoded code | P1, P2 | Grep for `+2`, `01[0125]`, `almasa`, Arabic literals outside `__()`; form test matrix |
| 2. Brittle title/slug/theme-mod lookups | P2, P3 | Rename every seeded page; all CTAs and images still resolve |
| 3. Widget/control ID instability | P3 (freeze before P4) | Schema doc exists; no ID diffs after P4 start |
| 4. Elementor Kit/fonts/Atomic/breakpoints | P1, P3 | Network shows no Google Fonts; Kit matches tokens; settings audit |
| 5. Editor JS lifecycle and empty states | P3 | Fresh-DB editor test; 10× control-change test |
| 6. Physical CSS, icons, bidi, RTL sliders | P1, P3, P5 | Logical-props lint; 360px overflow test; bidi sample page |
| 7. Arabic typography | P1, P3 | Rendered Fonts check; line-clamp visual check |
| 8. Font payload/loading | P1, P5 | Font bytes ≤ ~150 KB first view; CLS < 0.1 |
| 9. Hero LCP | P3, P5 | Lighthouse LCP element and timing on staging |
| 10. Contrast | P1, P3, P5 | Token role table; axe zero contrast violations |
| 11. 2.2.2 / 2.4.11 / 2.5.8 / dialogs | P3, P5 | Keyboard walkthrough at 390px; pause controls present |
| 12. Content integrity and photos | P4, P6 | Content ledger complete; client confirmation checklist signed |
| 13. Lead delivery/caching/spam | P2, P3, P6 | Production test submission to Gmail; cached-page test |
| 14. PDPL | P2, P4, P6 | Privacy page live; erasers registered; retention cron scheduled |
| 15. Deploy URL migration | P1, P6 | Runbook dry-run on staging; zero `localhost` hits |
| 16. Windows/XAMPP traps | P1, P4, P6 | `.gitattributes`/`.editorconfig` present; file-case script passes; utf8mb4 dump verified |
| 17. ACF Free workarounds | P2 | Single PHP source for field groups; settings survive deploy; `get_field` returns IDs |
| 18. Local SEO/schema/NAP/slugs | P2, P4, P5 | Rich Results Test; sitemap review; one H1 check |
| 19. Asset bloat | P1, P3, P5 | Requests/DOM/TBT budget on staging; no block-library CSS |
| 20. Media import pipeline | P1 (sizes), P4 | ASCII file names; no duplicates on re-run; alt text present |

## Sources

**First-party evidence (HIGH):**
- `C:\xampp\htdocs\al-masa\PROJECT_RULES.md` and theme code read on 2026-10-04: `inc/enqueue.php`, `inc/elementor.php`, `inc/elementor/class-almasa-widget.php`, `inc/media.php`, `inc/contact-form.php`, `inc/helpers.php`, `inc/seed-once.php`, `inc/setup.php`, `assets/js/theme.js`, `assets/css/motion.css`, `assets/css/site.css` (ripgrep scans), `template-parts/sections/hero.php`, `template-parts/sections/contact-form.php`, `theme.json`
- `C:\xampp\htdocs\mwmpower-ksa\.planning\PROJECT.md` (facts, brand tokens, scope)
- Contrast ratios calculated with the WCAG 2.x relative-luminance formula from the brand hex values

**Official documentation (HIGH/MEDIUM):**
- Elementor: Disable Google Fonts (https://elementor.com/help/disable-google-fonts/). Filter `elementor/frontend/print_google_fonts` in `includes/frontend.php` (github.com/elementor/elementor)
- Elementor: Inline Font Icons (https://elementor.com/help/inline-font-awesome/). Icons control and `Icons_Manager::render_icon()` (https://developers.elementor.com/docs/editor-controls/control-icons)
- Elementor: Widget optimization, `has_widget_inner_wrapper()` and `is_dynamic_content()` (https://developers.elementor.com/docs/widgets/widget-optimization/, https://developers.elementor.com/docs/widgets/widget-output-caching/, Elementor 3.22 developers update)
- Elementor: Global styles data structure (`__globals__`) (https://developers.elementor.com/docs/data-structure/global-styles/). Theme Style and disabling default colors/fonts (https://elementor.com/help/theme-style-global-settings/). Option names `elementor_disable_color_schemes`/`elementor_disable_typography_schemes` (elementor/elementor issue #1224)
- Elementor 4.0 / Atomic editor default on new installs (https://elementor.com/products/website-builder/v4-faq/, https://developers.elementor.com/elementor-editor-4-0-developers-update/)
- Elementor migration: Replace URL handles escaped slashes (`Utils::replace_urls` source) plus Regenerate CSS (https://elementor.com/blog/site-migration/). WP VIP note on JSON-escaped search-replace (https://docs.wpvip.com/vip-cli/wp-cli-with-vip-cli/search-replace/)
- SDAIA: Personal Data Protection Law, Implementing Regulation (notice-at-collection contents), Privacy Policy Guideline (https://sdaia.gov.sa/en/SDAIA/about/Documents/PersonalDataProtectionLaw.pdf, …/ImplementingRegulation.pdf, https://sdaia.gov.sa/Documents/PrivacyPolicyGuideline.pdf)
- web.dev font best practices (`unicode-range`, `size-adjust`) (https://web.dev/articles/font-best-practices)

**Secondary/community (MEDIUM/LOW, used only where consistent with the above):**
- Arabic web-font `unicode-range` blocks and subsetting guidance (voctos.net "Arabic Web Fonts and Page Speed"; sultanbyte.com "Arabic web fonts in production")
- Elementor experiments defaults table (Optimized Markup ≥3.30, Inline Font Icons ≥3.17), from a community skill write-up citing Elementor source. Verify on the installed version.
- Elementor GitHub issues on theme vs widget CSS order (#28728) and Google Fonts/eicons loading (#19091)

**Flagged as needing confirmation (not verified here):** Elementor's long-term support horizon for v3 custom widgets. Exact restrictions on using NCEC, Vision 2030 and Saudi Green Initiative logos. Kit container default padding and gap on the installed Elementor version. Whether toll-free/unified numbers should be accepted in the form (client decision).

---
*Pitfalls research for: Arabic RTL-first WordPress + Elementor Free + ACF Free B2B lead-generation site (Saudi environmental services)*
*Researched: 2026-10-04*
