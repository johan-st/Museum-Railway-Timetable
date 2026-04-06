# Style Guide – Museum Railway Timetable

Kodstandarder och clean code-principer för projektet.

---

## 1. Clean Code – Generella regler

### Läsbarhet
- **Namnge tydligt** – Variabler, funktioner och klasser ska beskriva sin syfte
- **Korta funktioner** – En funktion gör en sak, max ~50 rader
- **Max metodlängd** – Hård regel: max 50 rader per funktion/metod. Överskrids detta, dela upp i mindre funktioner (extract method)
- **Undvik djup nästling** – Max 3–4 nivåer; refaktorera vid behov
- **Kommentera varför, inte vad** – Koden ska vara självförklarande; kommentera affärslogik

### DRY (Don't Repeat Yourself)
- **Extrahera återanvändbar logik** till helper-funktioner
- **Undvik duplicerad kod** – Om samma logik finns på flera ställen, skapa en gemensam funktion

### Single Responsibility
- Varje funktion har ett ansvar
- Varje fil har ett tydligt syfte

### Förenkling
- **YAGNI** – Implementera inte saker "för framtiden"
- **KISS** – Välj den enklaste lösningen som fungerar
- **Undvik premature optimization** – Optimera först när det behövs

### Felhantering
- **Fail fast** – Upptäck fel tidigt, returnera eller kasta tydligt
- **Validera input** – Kontrollera data vid ingång till funktioner
- **Tydliga felmeddelanden** – Hjälp utvecklaren att förstå vad som gick fel

---

## 2. PHP

### Namnkonventioner
| Element | Konvention | Exempel |
|---------|------------|---------|
| Funktioner | `MRT_` + snake_case | `MRT_get_service_stop_times()` |
| Hooks (actions/filters) | `mrt_` prefix | `mrt_overview_days_ahead` |
| Meta keys | `mrt_` prefix | `mrt_service_number` |
| Post types | `mrt_` prefix | `mrt_station`, `mrt_timetable` |
| Taxonomier | `mrt_` prefix | `mrt_train_type` |

### Säkerhet
- **ABSPATH** – Alla PHP-filer (utom `uninstall.php`) ska ha: `if (!defined('ABSPATH')) { exit; }`
- **Escape all output** – Använd `esc_html()`, `esc_attr()`, `esc_url()` etc.
- **Sanitize input** – `sanitize_text_field()`, `intval()`, `wp_kses()` etc.
- **Nonces** – Alla formulär och AJAX-anrop ska använda nonces
- **Capability checks** – `current_user_can()` för admin-funktioner
- **SQL** – Alltid `$wpdb->prepare()` för parametriserade queries

### Dokumentation
- **PHPDoc** för alla funktioner med `@param`, `@return`, `@throws`
- **Text domain** – Alltid `museum-railway-timetable` för översättningar

### Övrigt
- **Inga inline styles** i PHP – använd CSS-klasser
- **Inga `echo` av oräddad data** – alltid escape först

---

## 3. CSS

### Namnkonventioner
- **Prefix** – Alla klasser: `.mrt-` (t.ex. `.mrt-timetable-overview`)
- **BEM-liknande** – `.mrt-block--modifier` (t.ex. `.mrt-btn--primary`)
- **Variabler** – CSS custom properties med `--mrt-` prefix

### Struktur
- **Design system** – Se `DESIGN_SYSTEM.md` för tokens
- **Komponentbibliotek** – Se `COMPONENT_LIBRARY.md` för återanvändbara komponenter (btn, form, badge, card m.fl.)
- **CSS-variabler** i `:root` för färger, spacing, borders
- **Mobile-first** – Basstilar för mobil, `@media (min-width)` för större skärmar
- **Inga inline styles** – All styling i CSS-filer

### Exempel
```html
<button class="button mrt-btn mrt-btn--primary">Spara</button>
<div class="mrt-card mrt-card--center">...</div>
<div class="mrt-input mrt-input--meta mrt-mt-1">...</div>
```

---

## 4. JavaScript

### Struktur
- **IIFE** – Wrappas i Immediately Invoked Function Expression
- **jQuery** – Använd `$` för DOM-manipulation
- **Ingen `console.log`** i produktion – endast med debug-flagga

### Namnkonventioner
- **camelCase** för variabler och funktioner
- **Prefix** för plugin-specifika: `mrtAdmin`, `mrtFrontend` etc.

### Event och AJAX
- **Nonces** – Skicka alltid med AJAX-anrop
- **Felhantering** – Hantera nätverksfel och visa användarvänliga meddelanden

### Delade util-moduler (`assets/mrt-*.js`)
- **`mrt-string-utils.js`** – `window.MRTStringUtils.escapeHtml` (XSS-säker text i HTML-strängar). **`admin-utils.js`** `escapeHtml` delegerar hit.
- **`mrt-date-utils.js`** – `window.MRTDateUtils` (format av `YYYY-MM-DD`, kalenderbyggstenar, `validateHhMm` för `HH:MM`). **`admin-utils.js`** `validateTimeFormat` delegerar till `MRTDateUtils.validateHhMm`.
- **`mrt-frontend-api.js`** – `window.MRTFrontendApi`: `getAjaxUrl`, `getNonce`, `msg` (strängar från `mrtFrontend`), `post` med valfri override av URL/nonce (t.ex. wizard). Laddas före `frontend.js`; används av `frontend.js` och kan användas av andra frontend-skript med samma beroenden.
- **Lägg ny återanvändbar logik** i rätt util-fil i stället för att duplicera i flera skript.
- **Enqueue** – Registreras i `inc/assets.php` (admin: bl.a. `mrt-string-utils` före `mrt-admin-utils`; frontend: `mrt-string-utils` + `mrt-frontend-api` före `mrt-frontend`, wizard beror på string + frontend-api utöver befintliga).

---

## 5. WordPress-specifikt

### Hooks
- **Actions** – `add_action('hook_name', 'callback', 10, 1)`
- **Filters** – `add_filter('mrt_filter_name', 'callback', 10, 2)`
- **Prefix** – Alla custom hooks: `mrt_`

### Översättning
- **Text domain** – `museum-railway-timetable`
- **Funktioner** – `__()`, `esc_html__()`, `esc_attr__()`, `_n()` etc.
- **Kontext** – Använd `_x()` vid behov för kontextberoende strängar

### Databas
- **Tabellprefix** – `$wpdb->prefix . 'mrt_stoptimes'`
- **Prepared statements** – Alltid för dynamiska queries

---

## 6. Filstruktur

### Mappar
- **Använd mappar** – Organisera kod i mappar efter ansvar (admin-ajax, admin-meta-boxes, shortcodes m.fl.)
- **En fil per ansvar** – Varje mapp innehåller filer med tydligt, sammanhörande ansvar
- **Loader-filer** – En huvudfil (t.ex. `admin-ajax.php`) kan require:a moduler från undermappar

### Struktur
```
museum-railway-timetable/
├── museum-railway-timetable.php   # Huvudfil
├── uninstall.php
├── docs/                          # Dokumentation (STYLE_GUIDE, COMPONENT_LIBRARY, etc.)
├── scripts/                       # deploy, validate, lint
├── inc/
│   ├── functions/                 # Helper-funktioner (helpers-*.php, timetable-view/)
│   ├── admin-ajax/                # AJAX-handlers (stoptimes, journey, timetable, route-*)
│   ├── admin-meta-boxes/          # Meta boxes (station, route, timetable, service)
│   ├── admin-page/                # Dashboard, clear-db, stats, routes
│   ├── cpt/                       # Custom post types, taxonomier
│   ├── shortcodes/                # shortcode-month, shortcode-journey, shortcode-overview
│   ├── assets.php
│   └── ...
├── assets/
│   ├── admin-*.css                # Uppdelad CSS (base, timetable, meta-boxes, dashboard, ui)
│   ├── admin.js
│   ├── mrt-string-utils.js        # MRTStringUtils.escapeHtml
│   ├── mrt-date-utils.js          # MRTDateUtils (datum/tid)
│   ├── mrt-frontend-api.js        # MRTFrontendApi (AJAX + mrtFrontend-meddelanden)
│   ├── admin-*.js                 # Moduler (utils, route-ui, stoptimes-ui, timetable-services-ui)
│   └── frontend.js
└── languages/
```

---

## 7. Contributing – Snabbchecklista

- [ ] Följer WordPress coding standards
- [ ] PHPDoc på alla nya funktioner
- [ ] All output escaped
- [ ] All input sanitized
- [ ] Nonces på formulär/AJAX
- [ ] Inga inline styles
- [ ] CSS-klasser med `.mrt-` prefix
- [ ] Funktioner med `MRT_` prefix
- [ ] Översättningsfunktioner med text domain
- [ ] Testerat manuellt

---

## 8. Referenser

- **COMPONENT_LIBRARY.md** – Komponenter och utilities
- **DESIGN_SYSTEM.md** – Tokens och base components
- **assets/CSS_STRUCTURE.md** – CSS-filstruktur
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [Clean Code (Robert C. Martin)](https://www.amazon.com/Clean-Code-Handbook-Software-Craftsmanship/dp/0132350882)
