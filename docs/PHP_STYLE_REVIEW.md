# PHP – Granskning mot Style Guide

Granskning av `*.php` mot STYLE_GUIDE.md (sektion 2. PHP).

**Senast analyserad:** 2025-02-17 (efter commit 2202bc3)

---

## ✅ Följs

| Krav | Status |
|------|--------|
| **ABSPATH** | Alla PHP-filer (utom uninstall.php, scripts/validate.php, phpstan-bootstrap) har `if (!defined('ABSPATH')) { exit; }` |
| **uninstall.php** | Använder `WP_UNINSTALL_PLUGIN` enligt WordPress-konvention |
| **Funktionsnamn** | `MRT_` + snake_case konsekvent |
| **Hooks** | `mrt_` prefix |
| **Meta keys, post types, taxonomier** | `mrt_` prefix |
| **Escape output** | `esc_html()`, `esc_attr()`, `esc_url()`, `esc_js()` används i output |
| **Sanitize input** | `sanitize_text_field()`, `intval()`, `sanitize_title()` på $_GET/$_POST |
| **Nonces** | Formulär och AJAX använder nonces, verifieras med wp_verify_nonce/check_ajax_referer |
| **Capability checks** | `current_user_can()`, `MRT_verify_ajax_permission()` för admin |
| **SQL** | `$wpdb->prepare()` för parametriserade queries |
| **Text domain** | `museum-railway-timetable` / MRT_TEXT_DOMAIN konsekvent |
| **PHPDoc** | Många funktioner har @param, @return |

---

## ⚠️ Förbättringar

### 1. Nonce – sanitize före wp_verify_nonce ✓ (genomfört)

- **route-destinations.php**, **route-stations.php**: Sanitize nonce med `sanitize_text_field(wp_unslash($_POST['nonce']))`

### 2. $_GET – sanitize ✓ (genomfört)

- **clear-db.php**: `$_GET['mrt_cleared']` sanitizes med `sanitize_text_field(wp_unslash())`

### 3. MRT_render_info_box – $content ✓ (genomfört)

- wp_kses_post($content) används för säker HTML-output

### 4. Inline styles ✓ (genomfört)

- hooks.php, service.php: Inline styles flyttade till admin-meta-boxes.css

### 5. PHPDoc – täckning ✓ (genomfört)

- **Åtgärd:** Lägg till PHPDoc på alla publika funktioner
- **Genomfört:** @param/@return tillagd på: route-stations.php, route-destinations.php, service-save.php, stoptimes.php, timetable-services.php, journey.php, timetable-frontend.php

---

## Filöversikt (urval)

| Fil/Område | Ansvar |
|------------|--------|
| museum-railway-timetable.php | Huvudfil, plugin bootstrap |
| inc/constants.php | MRT_TEXT_DOMAIN, post types, taxonomier |
| inc/assets.php | CSS/JS enqueue, localization |
| inc/cpt.php | Custom post types |
| inc/admin-ajax/*.php | AJAX-handlers (stoptimes, journey, timetable, route-destinations, route-stations) |
| inc/admin-meta-boxes/*.php | Meta boxes för station, route, timetable, service |
| inc/functions/*.php | Helpers (services, stations, routes, connections, datetime) |
| inc/shortcodes/*.php | shortcode-month, shortcode-journey, shortcode-overview |
| inc/admin-page/*.php | Dashboard, stats, routes, shortcodes |

---

## Mindre observationer (låg prioritet)

| Område | Fil | Kommentar |
|--------|-----|-----------|
| CSS-variabel inline | inc/functions/timetable-view/grid.php:81 | `style="--service-count: N"` – dynamisk layout, inte användardata. Acceptabelt för grid. |

---

## Sammanfattning

- **Struktur och grund:** Bra – följer STYLE_GUIDE och WordPress-konventioner
- **Prioritet 1–5 genomförda:** Sanitize nonce, $_GET, MRT_render_info_box (wp_kses_post), inline styles → CSS, PHPDoc-täckning
