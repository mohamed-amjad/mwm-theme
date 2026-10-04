# Feature Research

**Domain:** Saudi B2B environmental-services lead-generation website (NCEC-licensed remediation + pollution-control filters + industrial ventilation), Arabic RTL, WordPress custom theme edited with Elementor Free + ACF Free
**Researched:** 2026-10-04
**Confidence:** MEDIUM-HIGH (competitor patterns and regulatory/platform facts verified on live sources; conversion-rate claims are industry consensus, not measured)

---

## 0. How to read this file

- **Table stakes** = a Saudi facility manager or procurement officer expects it; missing it costs the lead.
- **Differentiators** = what makes MWM look more credible/easier to reach than competitors AND what makes this build clearly better than Al-Masa.
- **Anti-features** = deliberately not built (content-integrity rule, Elementor/ACF Free limits, performance, or scope).
- Complexity: **LOW** (< ½ day), **MEDIUM** (½–2 days), **HIGH** (> 2 days or cross-cutting).
- Every feature that prints a company fact follows the project rule **"empty field ⇒ UI hidden"** (Al-Masa `almasa_arg()` semantics).

---

## 1. Market observations (what competitors actually ship)

| Observation | Evidence | Implication for MWM |
|---|---|---|
| Licensed competitors print the **NCEC license class and number** as text (format `ELESL-YYYY-NNNNNN`) | Kayan Specialized "مرخّصة من المركز الوطني، فئة (أ) ترخيص ELESL-2024-000013"; EPCA license report shows `ELESL-2022-000095` with issue/expiry dates | License number + scope + expiry are first-class data, not an image only |
| NCEC runs a public **license search** and a **document verification** portal | `ecompliance.ncec.gov.sa` (search by commercial name / license no.) and `ecompliance.ncec.gov.sa/DocumentVerification/` (verification code) | "تحقق من الترخيص" link per license = strongest trust signal available without client names or stats |
| Filter competitors list **industries served** explicitly (crushers, cement, block, ready-mix, gypsum, metal) and the service verbs (توريد/تركيب/تشغيل/صيانة/قطع غيار) | بيئة المستقبل listing; الغلاف البيئي (getsa.net); AFT Saudi industry grid | Industries grid + service-verb structure are table stakes |
| Ventilation competitors sell the **site survey** ("معاينة واختيار القدرة الصحيحة — نحسب كمية الهواء والضغط") and show numbered process steps (01, 02, 03…) | saudi-ventilation.com | Site-visit request + process steps are expected |
| Quote forms use a **service-type select** (e.g. شفاطات مطابخ / تهوية مركزية / صيانة / استشارة / تركيب / طلب عرض سعر / أخرى) | smaartfan.com | Request type + service select are expected form fields |
| Floating **WhatsApp** + phone in header is universal; AFT shows "Chat with us on WhatsApp" with number | aftsaudi.com | WhatsApp float is table stakes; *two-number chooser* is not common → differentiator |
| Competitor sites lean on **years of experience, counters, testimonials** (AFT "27+ years"; smaartfan named testimonial) | aftsaudi.com, smaartfan.com | MWM cannot (no data) → compensate with verifiable licensing, real photos, process, scope detail |
| Most Saudi competitor sites are **old, slow, English-first or Arabic-machine-translated**, with no structured data | getsa.net (dated), AFT (English) | A fast, native-Arabic, schema-rich site is a real competitive edge in search |
| WhatsApp is the dominant channel (92.7% usage, Saudi Internet Report 2025) | GMCSCO citing CST report | WhatsApp gets equal or higher visual priority than the form |

---

## 2. Feature Landscape

### 2.1 Table Stakes (users expect these)

#### A. Pages & section inventory

| Feature | Why Expected | Complexity | Notes |
|---|---|---|---|
| **Home** — hero (slogan «خبراء جودة الهواء» + 3 CTAs: واتساب / اتصال / طلب عرض سعر) → NCEC license strip → 3 service-line cards → industries served → why MWM (grounded points) → work process → gallery teaser → branches/coverage → FAQ teaser → final CTA | Visitor must understand "licensed air-quality & remediation expert" in 5 seconds and act in one tap | MEDIUM | Each block = one MWM Elementor widget; hero LCP image static (no autoplay slider by default) |
| **من نحن** — page hero → about (client copy, expanded only with grounded facts) → licensing summary → capabilities (workshop for manufacturing filters, trained installation/maintenance team, engineers for ventilation design — all from PDF) → branches → CTA | Procurement checks who they are dealing with | LOW | Vision/mission optional ACF fields, hidden when empty (Al-Masa pattern) |
| **خدماتنا** (hub) — 3 service-line blocks, each listing its sub-services with anchor links into the detail page | Visitors scan scope before calling | LOW | Driven by service CPT tree |
| **Service detail page ×3** (تنفيذ خطط العلاج والتأهيل / فلاتر كبح الملوثات / أنظمة التهوية) — hero → intro → license badge for that field (if any) → sub-services grid → industries/applications → process steps → photo strip from that service line → FAQs for that service → service-specific CTA (WhatsApp text pre-filled with service name) | Each line is a separate buying decision and a separate search intent | MEDIUM | Biggest gap vs Al-Masa (it had no service detail pages). See §4 for Elementor approach |
| **معرض الأعمال** — gallery filterable by the 3 service lines (+ "الكل"), equal-ratio tiles, accessible lightbox, captions describing the activity (not invented project names) | Real field photos are the main proof when there are no client names | MEDIUM | ~25 PDF photos extracted at full resolution; attachment taxonomy (see §3) |
| **التراخيص والاعتمادات** — one card per license: issuing authority (NCEC), field/scope, license number, issue/expiry date (optional), certificate image/PDF (lightbox/download), "تحقق من الترخيص" link | Regulated buyers (crushers/plants facing NCEC compliance) must verify the contractor is licensed | LOW-MEDIUM | Both licenses exist; numbers/images not yet provided → cards render title + scope, hide the rest |
| **الفروع** — 3 branch cards (جازان، الرياض، الدمام): city/region, address, national short address, phones, working hours, "افتح في الخرائط" link, map only when `map_query` filled | Multi-city B2B buyers want the nearest branch and proof of national presence | LOW-MEDIUM | Also feeds footer + LocalBusiness schema. No invented coordinates |
| **طلب عرض سعر / معاينة** — dedicated form page (see §2.1-C) with WhatsApp/call alternatives beside it | Primary conversion page; ads and CTAs point here | MEDIUM | Same form widget reusable in other pages |
| **تواصل معنا** — phones (both), email, WhatsApp, branches summary, short contact form, social icons | Expected even when a quote page exists | LOW | Reuse widgets |
| **سياسة الخصوصية** page | Saudi PDPL requires a published privacy notice before collecting personal data via forms | LOW | Seed editable draft; linked from form consent + footer |
| **404** page with CTAs | Broken links from WhatsApp shares happen | LOW | |
| Header: logo, WP menu, phone + WhatsApp + "اطلب عرض سعر" button; transparent-on-hero → solid on scroll; accessible mobile drawer | Universal | MEDIUM | Al-Masa pattern, improved (see differentiators) |
| Footer: logo, short about, menu, services list, branches, phones/email, social, CR/VAT/license numbers when filled, privacy link | Saudi B2B footers routinely show CR/VAT; procurement copies them | LOW | Data from global settings |
| Breadcrumbs on inner pages | Orientation + BreadcrumbList schema | LOW | |

#### B. Trust signals (no client names / stats / project names available)

| Feature | Why Expected | Complexity | Notes |
|---|---|---|---|
| **NCEC licensing shown prominently** (home strip, licenses page, service pages, footer) in the two exact fields: تنفيذ خطط العلاج والتأهيل + فلاتر كبح الملوثات | It is the single legal differentiator in this market | LOW | Do not claim licensing for ventilation (not licensed field) |
| **Real field photos** with honest captions (loaders/dozers on rehabilitation sites, spill remediation, filter installation, bag-filter spares, valves, duct/fan/odor units) | Proves real operations | MEDIUM | Extract originals from `services.pdf`, never page screenshots; alt text in Arabic |
| **Industries served** grid: الكسارات والمحاجر، المواقع التعدينية، مصانع البلوك، الخرسانة الجاهزة، المصانع، الطرق (حوادث الشاحنات/الانسكابات)، المنشآت التجارية والخدمية | Buyer self-identifies; matches competitor norm | LOW | All grounded in PDF; editable repeater in widget |
| **Work process steps** (e.g. معاينة الموقع → التصميم/الخطة → التوريد والتصنيع → التركيب/التنفيذ → الصيانة والمتابعة) | Replaces "years of experience" with method; standard in ventilation competitors | LOW | Generic, editable; per-service variant allowed |
| **Capability facts** from the PDF: ورش تصنيع الفلاتر محلياً، فريق مدرب للتركيب والصيانة الدورية، مهندسون مختصون في التهوية، قطع غيار لجميع خامات Bag Filters (قماشية وفايبرجلاس)، صمامات من ماركات معروفة مع الضمان | Concrete, verifiable claims beat adjectives | LOW | "Why MWM" widget; no numbers |
| **National coverage** — three branches + "نعمل في جميع أنحاء المملكة" | Buyers outside the 3 cities need reassurance | LOW | Text + branch list (map visual is a differentiator, below) |
| **FAQ** (per service + general) | Answers objections (do you serve my city? maintenance contracts? spare-part materials? who prepares vs executes rehabilitation plans?) | LOW-MEDIUM | FAQ CPT; content needs client confirmation (see Gaps) |
| **Company identity data** — CR, VAT, unified number, national address — shown when filled | Saudi procurement/vendor registration requires them; signals a real establishment | LOW | Global settings |

#### C. Conversion UX (Saudi market)

| Feature | Why Expected | Complexity | Notes |
|---|---|---|---|
| **Floating WhatsApp** offering **both numbers** (visitor chooses; editable labels per number) | WhatsApp is the default B2B first contact in KSA | MEDIUM | `https://wa.me/966573825004?text=…`; accessible popover (button + `aria-expanded`, Esc closes, focus trap-light) |
| **Pre-filled WhatsApp message per page** (e.g. «السلام عليكم، أرغب في الاستفسار عن خدمة: فلاتر كبح الملوثات — من صفحة …») | Tells sales which service/page the lead came from; removes "hi" friction | LOW | Template in global settings with `{service}` / `{page}` tokens; URL-encoded |
| **`tel:` links everywhere** (header, hero, branches, footer, sticky bar) in `+966` format | Mobile users call directly | LOW | Display local `057 382 5004`, link international; `dir="ltr"` on numbers |
| **Sticky mobile action bar** (اتصال / واتساب / عرض سعر) on ≤ 768px | Thumb-reach one-tap contact; standard on GCC service sites | LOW-MEDIUM | Respect `env(safe-area-inset-bottom)`; hide when the quote form is in view or keyboard open; no overlap with floating WhatsApp (bar replaces float on mobile) |
| **Quote / site-visit form** fields: الاسم*، الجوال* (Saudi validation `05XXXXXXXX` / `+9665…`)، اسم المنشأة، نوع المنشأة (كسارة/محجر، مصنع بلوك، خرسانة جاهزة، مصنع، موقع تعديني، منشأة تجارية/خدمية، جهة طرق/نقل، أخرى)، المدينة/المنطقة (13 regions or city list)، الخدمة (from service CPT tree)، نوع الطلب (عرض سعر، معاينة موقع، عقد صيانة، قطع غيار، استشارة)، وسيلة التواصل المفضلة (واتساب/اتصال/بريد)، البريد (اختياري)، تفاصيل الطلب | Qualifies the lead before the first call; mirrors competitor selects | MEDIUM | Selects driven by data (CPT + editable option lists), not hardcoded |
| **PDPL consent checkbox** (unticked, required) linking privacy policy; consent timestamp stored with the request | PDPL Implementing Regulation Art. 11: consent must be specific, documented, not pre-ticked; privacy notice before collection | LOW | Store `consent_at`, IP hash, policy URL |
| Form security: nonce, honeypot, per-IP throttle, server-side validation mirroring client, no-JS fallback | Al-Masa baseline; spam kills inbox trust | LOW (reuse) | Port Al-Masa `contact-form.php` |
| Success state with next step ("سنتواصل معك… أو تواصل فوراً عبر واتساب") + WhatsApp button | Keeps momentum; reduces duplicate submissions | LOW | Inline, focus moved to message for a11y |
| Email notification to configurable recipient list, `Reply-To` = submitter | Owner must see leads immediately | LOW | Recipients in global settings (not `admin_email` only). Needs SMTP in production |

#### D. Admin / editing UX

| Feature | Why Expected | Complexity | Notes |
|---|---|---|---|
| **One global settings screen** «إعدادات الموقع» (custom admin page via Settings API — ACF Options page is Pro) with tabs: التواصل (phone 1/2 + labels, WhatsApp 1/2 + labels, email, form recipients, default WhatsApp message), الشركة (legal name AR/EN, CR, VAT, unified no., national address), السوشيال (X, LinkedIn, Instagram, Snapchat, TikTok, YouTube, Facebook), الأزرار العائمة (toggles, which number first, show on pages), الملفات (company profile PDF), SEO الافتراضي (default OG image) | Client must change a phone once and see it everywhere; explicitly requested | MEDIUM | Single source for header, footer, floating buttons, sticky bar, schema, form emails |
| **Service CPT** (hierarchical: 3 lines → sub-services) with ACF Free fields: short summary, icon (image/SVG from media), related license (Post Object/Relationship — available in ACF Free), WhatsApp message override, order | Structured, reusable across hub, details, form select, gallery terms, FAQ | MEDIUM | Seed all PDF content (every bullet) |
| **Branch CPT**: city, region, address, national short address (`AAAA9999`), phone/WhatsApp override, working hours (text), `map_query`, HQ flag, order | Feeds branches page, footer, form city list, schema | LOW-MEDIUM | |
| **License CPT**: authority, field, number, class, issue/expiry dates, certificate image, certificate PDF, verification URL/code, linked service line | Licenses page, home strip, service badges | LOW-MEDIUM | |
| **FAQ CPT** (or taxonomy-scoped): question, answer, linked service(s), order | Per-service FAQs without ACF Repeater | LOW | |
| **Request inbox CPT** (not public): list columns (date, name, phone, facility type, city, service, request type, status), unread bubble, read-only detail metabox | Al-Masa baseline, extended with new fields | MEDIUM | Port + extend Al-Masa `almasa_request` |
| **Every widget prefilled with live values; empty = hidden; data-driven widgets show "edit records" links** | Non-technical client edits in place without breaking layout | MEDIUM (reuse) | Al-Masa `almasa_arg()` + `data_note()` |
| **Elementor Kit** global colors (7 brand tokens) + global fonts mirror CSS tokens | Client restyles consistently | LOW | |
| Header/footer editable as saved Elementor section templates picked in settings | Theme Builder is Pro; proven Al-Masa workaround | LOW (reuse) | Move the picker into the global settings screen instead of Customizer |

#### E. SEO / accessibility / performance

| Feature | Why Expected | Complexity | Notes |
|---|---|---|---|
| Semantic HTML, one H1 per view, `lang="ar" dir="rtl"`, skip link, visible focus, keyboard-operable menu/lightbox/popover/filters, form errors linked with `aria-describedby` | WCAG 2.2 AA is a stated requirement | MEDIUM | Brand `#5BBF75` never for text on white |
| `prefers-reduced-motion` honored | Requirement + a11y | LOW | |
| Document title, meta description (from excerpt/field), canonical, Open Graph + Twitter cards (WhatsApp link previews are how B2B pages get shared in KSA) | Shared links must preview with logo/photo and Arabic title | LOW-MEDIUM | Auto-disable when Yoast/Rank Math active |
| JSON-LD: `Organization` (home) + `LocalBusiness` per branch with `parentOrganization`; `Service` on service pages; `BreadcrumbList` | Google guidance: Organization on home, one LocalBusiness per location linked via `parentOrganization` | MEDIUM | Only filled properties emitted; no `geo` unless provided |
| WP core XML sitemap including CPT singles; exclude request/FAQ/license CPTs if not public | Indexation | LOW | |
| **Latin slugs** for URLs (`/services/dust-filters/`) with Arabic titles | Arabic slugs percent-encode into unreadable, very long URLs when pasted in WhatsApp/emails | LOW | Editorial rule + seeded slugs |
| Lighthouse mobile ≥ 90: self-hosted Arabic font subset + `font-display: swap`, WebP/AVIF, responsive `srcset`, `fetchpriority="high"` on LCP hero image, lazy below the fold, no jQuery sliders, Elementor asset optimizations (disable Elementor Google Fonts / unused icon libraries, improved asset loading) | Stated requirement; most competitors are slow | MEDIUM | |
| Map **click-to-load facade** (static placeholder → iframe on click) | Google Maps iframes cost ~500 KB+ and set third-party cookies | LOW | Only when `map_query` filled |

---

### 2.2 Differentiators (competitive advantage + "better than Al-Masa")

| Feature | Value Proposition | Complexity | Notes |
|---|---|---|---|
| **"تحقق من الترخيص" verification links** on each license card (to NCEC e-compliance search / document verification with code) | Turns "trust us" into "verify us" — beats competitors' counters and testimonials, and needs no invented data | LOW | Shown only when the URL/code is filled |
| **License ↔ service mapping**: the licensed field badge appears on the matching service page and card ("مرخصة من المركز الوطني للرقابة على الالتزام البيئي في هذا المجال") | Immediately answers the compliance question on the page the buyer is reading | LOW | Relationship field on service/license |
| **License expiry awareness** — admin notice X days before `expiry_date`; expired licenses auto-hide public expiry or flag in admin | Prevents showing an expired license (reputational/legal risk); competitors' license reports show 3-year validity | LOW | |
| **Two-number WhatsApp chooser with labels + page-aware pre-filled text** | Visitor picks the right contact; sales knows source page/service instantly | MEDIUM | Tokens `{service}`, `{page}`, `{city}` |
| **Spare-parts quick request** block on the filters page (Bag filters: قماشية/فايبرجلاس; Valves) with "اطلب القطعة عبر واتساب" (pre-filled with part type) and a form preset `نوع الطلب = قطع غيار` | Repeat-revenue path competitors bury in text; short buying cycle | LOW | Confirm "Solenoid Valve" wording with client |
| **Maintenance-contract CTA** (`نوع الطلب = عقد صيانة`) on filters page | Recurring revenue line from PDF; competitors mention but don't capture it | LOW | Preset via URL param `?type=maintenance` |
| **Form presets via URL params** (`?service=…&type=…`) so every CTA pre-selects the form | Fewer fields to touch → higher completion; better lead data | LOW | |
| **Lead attribution stored with each request**: source page, referrer, UTM params, device | Owner can see which service/page/ads produce leads (Al-Masa stored only `request_page`) | LOW | Hidden fields + server capture |
| **Inbox workflow**: status (جديد / تم التواصل / تم إرسال عرض / مغلق) with quick edit + filter, admin "رد عبر واتساب" and "اتصال" buttons using the submitted phone, CSV export | Turns the inbox into a lightweight CRM; Al-Masa inbox was read-only | MEDIUM | Status as meta + admin column filter; export capability-checked |
| **Click-event instrumentation** (`dataLayer.push` for `whatsapp_click` {number, page}, `call_click`, `quote_submit`) + optional GA4/GTM ID field in settings (hidden/no script when empty) | Measures WhatsApp/call leads that never touch the form | LOW | No analytics script loads unless the client fills an ID |
| **KSA coverage visual** — lightweight inline SVG map of the Kingdom with the 3 branch cities highlighted + "نخدم جميع مناطق المملكة" | Communicates national reach without stats; reinforces branch credibility | MEDIUM | Stylized positions, not coordinates; city list from branch CPT |
| **Company profile PDF download** («تحميل ملف الشركة») from settings | B2B procurement teams ask for a profile; services PDF already exists | LOW | Button hidden when no file |
| **Per-service detail pages editable in Elementor** (service CPT with Elementor support enabled — allowed in Free via Elementor → Settings → Post Types) with seeded layouts and a PHP fallback | Al-Masa singles were PHP-only; here the client edits service pages visually | MEDIUM-HIGH | Layouts thin (widgets read CPT data) so updates rarely need per-page edits |
| **Deeper widget controls than Al-Masa**: every widget has Content (all copy, prefilled), Style (colors from Kit, typography, spacing, background/overlay, border/radius), Layout (columns per breakpoint, alignment), per-element "إظهار" toggles | Explicit client priority: "change anything without a developer" | HIGH (cross-cutting) | Shared control helpers in the base widget class to avoid duplication |
| **In-dashboard editing guide** — dashboard widget «دليل تعديل الموقع» with links: global settings, services, branches, licenses, gallery categories, requests, "edit home in Elementor" | Non-technical client finds things without calling the developer | LOW | |
| **Gallery categories assignable from the Media Library** (attachment taxonomy = service lines; bulk assign) + widget auto-pulls by category, optional manual override | Client adds a new field photo in two clicks; no widget editing required | MEDIUM | Better than Al-Masa per-widget tab repeaters that diverge per page |
| **Native-Arabic copywriting** grounded in PDF, with technical English terms kept where buyers search them (Bag Filters, Duct, Solenoid Valve) | Competitors are English-first or machine-translated; matches real search queries | LOW | Copy review by client |
| **Premium but light motion** (reveal/stagger, subtle "air flow" leaf-pattern band from PDF footer motif), reduced-motion safe | Brand feel ("clean air") without JS weight | MEDIUM | CSS + IntersectionObserver only |
| **Schema per branch + Service schema** | Rare among Saudi competitors; local-pack and rich-result eligibility in Jazan/Riyadh/Dammam | MEDIUM | |

---

### 2.3 Anti-Features (deliberately NOT built)

| Feature | Why Requested | Why Problematic | Alternative |
|---|---|---|---|
| Animated counters ("+500 مشروع", "+15 سنة") | Every competitor has them | No data; violates content-integrity rule; buyers distrust round numbers | License verification + process + real photos. A stats widget may exist only if it renders nothing until real numbers are entered (P3) |
| Client logo wall / testimonials | Social proof | None provided; fake testimonials are a legal/reputational risk | Add `mwm_project`/testimonial CPT later when real ones exist (v1.x) |
| Named projects / case studies | Portfolio norm | No project names/clients yet | Gallery by service line with activity captions |
| NCEC / government logos used as decoration | Looks official | Government emblem use usually needs permission; implies endorsement | Text "مرخصة من المركز الوطني للرقابة على الالتزام البيئي" + client's own certificate image + verify link |
| Claiming licensing for ventilation | Uniform badge on all services | Only two fields are licensed | Badge only where a license record is linked |
| City × service landing pages (e.g. "فلاتر كسارات في جازان", "… في الرياض") | Local SEO hacks | Thin/doorway pages, duplicate content risk | One branch section + LocalBusiness per branch + "نخدم جميع المناطق" copy |
| Separate page per sub-service in v1 (~20 pages) | SEO breadth | Thin content (one-line bullets in PDF), maintenance burden | Sub-services as anchored blocks on the 3 detail pages; promote a sub-service to its own page later when it has real content (spare parts is the first candidate) |
| File upload in the quote form | "Send us photos of your site" | Security surface, spam/malware, storage, PDPL scope | "أرسل صور الموقع عبر واتساب" button next to the form |
| Live chat widget / chatbot / third-party WhatsApp plugins | Engagement | Heavy JS, hurts Lighthouse, duplicates WhatsApp | Native floating WhatsApp + sticky bar (theme code, ~2 KB) |
| Auto-playing hero video or heavy slider | "Premium" look | LCP/CLS and data cost on mobile; a11y | Static hero photo with subtle motion; optional scroll-snap slider (Al-Masa pattern) that pauses correctly |
| Popups / exit-intent | Lead capture | Popup Builder is Pro; intrusive; Google discourages on mobile | Sticky action bar + contextual CTAs |
| Full SEO plugin features in the theme (per-page SEO meta box suite, redirects) | Convenience | Re-implements Yoast/Rank Math poorly | Theme emits essentials and steps aside when an SEO plugin is active |
| Blog/news | Content marketing | Out of scope v1 | Revisit v2 |
| English toggle in v1 | Future market | Out of scope; half-translated sites hurt trust | Translation-ready strings, `en` later |
| Online pricing / calculators / e-commerce for spare parts | Self-service | B2B quoted pricing; payments out of scope | Spare-parts request via WhatsApp/form |
| Search page | Generic | ~10 pages; adds noise | Clear nav + services hub |
| FAQ rich-result promises | SEO | Google restricted FAQ rich results (2023) to authoritative gov/health sites | Keep FAQ for users; `FAQPage` markup optional, low value |
| Cookie banner by default | Compliance reflex | No tracking cookies unless analytics ID is filled; maps behind facade | Add consent banner only when analytics is enabled (v1.x) |

---

## 3. Data model implied by the features (ACF Free compatible)

| Record | Storage | Public? | Feeds |
|---|---|---|---|
| Global settings | Custom admin page (Settings API, one option array) | — | Header, footer, float, sticky bar, form recipients, WhatsApp templates, schema, OG default, header/footer template pick |
| Service (`mwm_service`, hierarchical) | CPT + ACF Free (text, textarea, image, Post Object/Relationship, number) + Elementor support | Yes (`/services/{slug}/`) | Hub, detail pages, cards, form select, FAQ/license relations, Service schema |
| Gallery category (`mwm_gallery_cat`) | Taxonomy on `attachment` (terms = service lines) | No archive needed | Gallery widget filters, service photo strips |
| Branch (`mwm_branch`) | CPT + ACF | No single needed (anchors on branches page) | Branches page, footer, form city list, LocalBusiness schema, coverage map |
| License (`mwm_license`) | CPT + ACF (file field for PDF is Free) | No single needed | Licenses page, home strip, service badges, footer numbers, expiry notice |
| FAQ (`mwm_faq`) | CPT + ACF relation to services | No | Service pages, home/general FAQ widget |
| Request (`mwm_request`) | CPT, not public, created only by form | No | Inbox, email, CSV export |
| Pages | WP pages built with MWM widgets | Yes | — |

ACF Free limits honored: no Repeater/Gallery/Flexible/Options → repeatables are CPTs; galleries are attachment taxonomy or Elementor Gallery control inside widgets; settings are a custom admin page. Elementor Free limits honored: no Theme Builder/Form/Popup/Loop/Global Widgets/dynamic tags → custom widgets read data in PHP; header/footer via saved templates; form is theme code.

---

## 4. Elementor editing approach for service detail pages (decision input)

**Recommendation:** enable Elementor on the `mwm_service` CPT, seed each of the 3 main service posts with an Elementor layout composed of MWM widgets that *read the current post's CPT data by default* (title, summary, sub-services = child posts, photos = gallery category, FAQs = related, license = related). Fallback `single-mwm_service.php` renders the same template parts if Elementor content is empty.

- Why not plain Pages: loses the parent/child data relationship needed by the hub, form select, FAQ and license mapping.
- Why not PHP-only singles (Al-Masa): fails the "client edits everything in Elementor" priority.
- Risk: Free has no shared single template, so a layout change must be made on 3 posts → acceptable at 3 pages; keep layouts thin and data-driven. Sub-services stay as blocks (no own Elementor pages) in v1.

---

## 5. Feature Dependencies

```
Design tokens + Elementor Kit
    └──required by──> every MWM widget (Style controls default to Kit globals)

Global settings screen
    ├──required by──> Header / Footer widgets
    ├──required by──> Floating WhatsApp chooser ──> Sticky mobile action bar (shares config; bar replaces float ≤768px)
    ├──required by──> Form email notifications (recipients) + success-state WhatsApp CTA
    ├──required by──> Organization schema / OG defaults
    └──required by──> Click instrumentation (optional GA4/GTM ID)

Service CPT (+ Elementor support, seeded PDF content)
    ├──required by──> Services hub, service detail pages, service cards (home)
    ├──required by──> Quote form "الخدمة" select + URL presets (?service=)
    ├──required by──> Page-aware WhatsApp text ({service})
    ├──required by──> Service schema
    └──enhanced by──> License CPT (badge) · FAQ CPT (per-service FAQ) · Gallery taxonomy (photo strip)

PDF photo extraction → Media Library (+ alt text)
    └──required by──> Gallery taxonomy assignment ──> Gallery page, service photo strips, hero/section imagery

Branch CPT
    ├──required by──> Branches page, footer branches, form "المدينة" list
    ├──required by──> LocalBusiness schema (parentOrganization → Organization)
    └──required by──> KSA coverage SVG map · Map click-to-load facade (only when map_query filled)

License CPT
    └──required by──> Licenses page, home license strip, service badges, verify links, expiry admin notice

Privacy policy page
    └──required by──> PDPL consent checkbox ──> Quote / contact form

Request CPT (inbox)
    └──required by──> Quote form submit ──> status workflow, CSV export, admin WhatsApp/call reply, attribution fields

Base widget class (prefill + empty=hidden + data notes + shared style controls)
    └──required by──> all section widgets

Elementor-built pages ──conflicts with── hardcoded PHP section maps (keep PHP only as fallback)
Autoplay hero slider ──conflicts with── Lighthouse 90+ / reduced motion (default static hero)
Map iframes ──conflicts with── performance + privacy (facade required)
Floating WhatsApp ──conflicts with── sticky mobile bar (show one per breakpoint)
Transparent header ──requires── first widget = hero/page-hero (body class, Al-Masa pattern)
```

### Dependency notes

- **Global settings before widgets:** header/footer/float/sticky/form all read it; building widgets first leads to hardcoded phones.
- **Service CPT before forms and gallery:** form selects, WhatsApp tokens and gallery terms derive from it; seeding must be idempotent (Al-Masa seed-once).
- **Photo extraction before gallery and service pages:** the gallery and every service page's proof depend on real photos.
- **Privacy page before form launch:** PDPL consent links to it; launching the form without it is a compliance gap.
- **License data is optional at launch:** cards degrade gracefully (title + scope) until numbers/certificates arrive.

---

## 6. MVP Definition

### Launch With (v1)

- [ ] Global settings screen (contacts, two WhatsApp numbers + labels, email, recipients, company IDs, social, header/footer pick, profile PDF, WhatsApp template) — single source of truth
- [ ] CPTs: services (hierarchical, Elementor-enabled), branches, licenses, FAQs, requests; attachment gallery taxonomy — all PDF content seeded
- [ ] Pages: home, about, services hub, 3 service detail pages, gallery, licenses, branches, quote/site-visit, contact, privacy, 404
- [ ] Widgets with prefilled content + full style/layout controls + empty-hides semantics
- [ ] Floating two-number WhatsApp with page-aware text; sticky mobile action bar; `tel:` everywhere
- [ ] Quote form with facility type / city / service / request type / preferred contact, PDPL consent, URL presets, attribution, email notification
- [ ] Inbox with status, filters, CSV export, WhatsApp/call reply buttons
- [ ] License cards with verify links + service badges + expiry notice
- [ ] Industries served, process steps, "why MWM" (grounded), FAQ
- [ ] Schema (Organization, LocalBusiness ×3, Service, Breadcrumb), OG/Twitter, sitemap, Latin slugs
- [ ] WCAG 2.2 AA, reduced motion, Lighthouse mobile 90+, map facade
- [ ] Dashboard editing guide

### Add After Validation (v1.x)

- [ ] `mwm_project` CPT + case-study single — trigger: client supplies first named project with permission
- [ ] Testimonials / client logos — trigger: real, permissioned quotes/logos
- [ ] Dedicated spare-parts page (bag filters by material, valves by type) — trigger: client provides part catalogue/brands
- [ ] GA4/GTM + consent banner — trigger: client wants analytics or runs ads
- [ ] Request auto-reply email to submitter + retention auto-purge setting — trigger: steady lead volume

### Future Consideration (v2+)

- [ ] English version (translation-ready strings already) — client decision
- [ ] Blog/knowledge base on NCEC compliance topics — needs ongoing content
- [ ] Stats/counters — only with verified numbers
- [ ] WhatsApp Business API / CRM integration — when lead volume justifies

---

## 7. Feature Prioritization Matrix

| Feature | User Value | Implementation Cost | Priority |
|---|---|---|---|
| Global settings screen | HIGH | MEDIUM | P1 |
| Floating two-number WhatsApp + page-aware text | HIGH | MEDIUM | P1 |
| Sticky mobile action bar | HIGH | LOW | P1 |
| `tel:` links / header CTAs | HIGH | LOW | P1 |
| Quote/site-visit form + PDPL consent + inbox | HIGH | MEDIUM | P1 |
| Service CPT + 3 detail pages (Elementor-editable) | HIGH | MEDIUM-HIGH | P1 |
| NCEC licenses page + home strip + service badges | HIGH | LOW-MEDIUM | P1 |
| Gallery by service line (real photos, lightbox) | HIGH | MEDIUM | P1 |
| Branches page + LocalBusiness schema | HIGH | MEDIUM | P1 |
| Industries served / process / why-MWM widgets | MEDIUM-HIGH | LOW | P1 |
| Widget style/layout controls (deeper than Al-Masa) | HIGH (client) | HIGH | P1 |
| Performance + a11y baseline | HIGH | MEDIUM | P1 |
| OG/Twitter + Organization/Service/Breadcrumb schema | MEDIUM | MEDIUM | P1 |
| FAQ CPT + per-service FAQs | MEDIUM | LOW | P1 |
| License verify links + expiry notice | HIGH | LOW | P1 |
| Inbox status / CSV / WhatsApp reply / attribution | MEDIUM-HIGH | MEDIUM | P1 |
| Form URL presets + spare-parts / maintenance CTAs | MEDIUM | LOW | P1 |
| Dashboard editing guide | MEDIUM (client) | LOW | P2 |
| KSA coverage SVG map | MEDIUM | MEDIUM | P2 |
| Company profile PDF download | MEDIUM | LOW | P2 |
| Click instrumentation + optional GA4 field | MEDIUM | LOW | P2 |
| Map click-to-load facade | MEDIUM | LOW | P2 (P1 if any map filled) |
| Project CPT / testimonials | MEDIUM | MEDIUM | P3 (blocked on real data) |
| Stats widget (empty by default) | LOW | LOW | P3 |

---

## 8. Competitor & Baseline Feature Analysis

| Feature | Saudi competitors (AFT Saudi, الغلاف البيئي, بيئة المستقبل, حلول أنظمة التهوية, Kayan) | Al-Masa (shipped baseline) | MWM approach |
|---|---|---|---|
| License display | Text claim; Kayan shows class + number | n/a | Cards with number, scope, certificate, **verify link**, expiry awareness, service badges |
| Service detail | Long single pages or product lists | Service CPT = title + image only; no detail pages | 3 Elementor-editable detail pages with sub-services, process, photos, FAQ, CTA |
| Industries served | Common list/grid | None | Grid grounded in PDF |
| Process / site survey | Numbered steps (ventilation sites) | None | Process widget + "معاينة موقع" request type |
| WhatsApp | Single-number float | WhatsApp only as a social icon | Two-number chooser, page-aware text, sticky mobile bar |
| Quote form | Service select, basic fields | name/phone/email/service/message | + facility type, city, request type, preferred contact, PDPL consent, presets, attribution |
| Inbox | Unknown/email only | Read-only CPT inbox + unread bubble | + status workflow, filters, CSV, WhatsApp/call reply |
| Gallery | Static images | Per-widget repeater tabs (diverge per page) | Media-library taxonomy, auto-pulled everywhere, filters + lightbox |
| Branches | Text address | Locations CPT + embeds on contact page | Branches page, national address, hours, facade maps, LocalBusiness schema, coverage map |
| Global contacts | n/a | Social in Customizer; phones as CPT records | One «إعدادات الموقع» screen for everything |
| SEO | Mostly none | `title-tag` only; OG/schema deferred | OG/Twitter, Organization + LocalBusiness×3 + Service + Breadcrumb, Latin slugs |
| Trust without data | Years/counters/testimonials | Figures derived from CPT counts | Verification, real photos, capabilities, process — no numbers |
| Performance/a11y | Generally slow/dated | Good baseline, not measured to target | Lighthouse 90+ mobile, WCAG 2.2 AA as acceptance criteria |

---

## 9. Gaps / items needing client confirmation

- License numbers, class, issue/expiry dates, certificate scans, and NCEC verification codes (fields ship empty).
- "Sonolite Valve" → almost certainly **Solenoid Valve**; confirm wording and brands for the valves block.
- Branch addresses, national short addresses, working hours, which branch is HQ, and whether each number belongs to a branch/department (drives WhatsApp labels).
- FAQ answers (e.g. maintenance-contract terms, coverage outside the 3 cities, whether MWM prepares plans or only executes NCEC-approved ones — license is for *execution*).
- Social accounts, CR, VAT, unified number.
- Permission to state "ورش تصنيع" location/city and "الضمان" terms precisely.
- Whether a company profile PDF (separate from services PDF) exists.

---

## Sources

- Kayan Specialized (LinkedIn) — NCEC class (A) license number format `ELESL-2024-000013` — HIGH (primary)
- EPCA environmental-services license report PDF (epca.sa) — license number/issue/expiry format — HIGH
- NCEC e-services: remediation-execution license (`ncec.gov.sa/.../Eservice006.aspx`), consultancy license — HIGH
- NCEC e-compliance license search (`ecompliance.ncec.gov.sa/NCEC/Cap/CapHome.aspx`) and Document Verification (`ecompliance.ncec.gov.sa/DocumentVerification/`) — HIGH
- AFT Saudi (aftsaudi.com) — industries grid, WhatsApp float, experience claims — HIGH (observed)
- مؤسسة الغلاف البيئي (getsa.net); بيئة المستقبل listing (opensooq) — filter service verbs, industries — MEDIUM
- حلول أنظمة التهوية (saudi-ventilation.com), smaartfan.com — process steps, site survey, quote form service select — HIGH (observed)
- SDAIA PDPL Implementing Regulation (Art. 11 consent) and Privacy Policy Guideline — HIGH
- Google Search Central: Organization and LocalBusiness structured data; schema.org `parentOrganization`/`branchOf` — HIGH
- Elementor help "Pro vs Free" + Elementor blog — Free lacks Theme Builder, Form, Popup, Loop, Global Widgets, dynamic content — HIGH
- WhatsApp click-to-chat format `wa.me/<intl number>?text=` (WhatsApp Help Center, cited by eclipseadagency / mansion-tec) — HIGH
- GMCSCO citing Saudi Internet Report 2025 (WhatsApp 92.7% usage) — MEDIUM (secondary)
- Saudi Post (SPL) national address short code = 4 letters + 4 digits — HIGH
- Saudi Business Center / MoC — e-commerce authentication applies to e-stores (not needed for this B2B service site) — HIGH
- Google 2023 FAQ rich-result restriction — MEDIUM (from training knowledge, not re-verified this session)
- Al-Masa `PROJECT_RULES.md` §8–9a and theme code (`inc/contact-form.php`, `inc/social.php`, `template-parts/sections/contact-form.php`) — HIGH (direct read)

---
*Feature research for: Saudi B2B environmental services / industrial filtration & ventilation lead-gen website*
*Researched: 2026-10-04*
