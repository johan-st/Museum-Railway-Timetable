# JavaScript – Granskning mot Style Guide

Granskning av `assets/*.js` mot STYLE_GUIDE.md och COMPONENT_LIBRARY.md.

**Senast analyserad:** 2025-02-17 (efter commit 2202bc3)

---

## ✅ Följs

| Krav | Status |
|------|--------|
| **IIFE** | Alla filer wrappas i `(function($) { ... })(jQuery)` |
| **jQuery** | Använder `$` för DOM |
| **console.log** | Endast bakom `window.mrtDebug` |
| **camelCase** | Variabler och funktioner använder camelCase |
| **Prefix** | `mrtAdmin`, `mrtFrontend` för lokaliserade strängar |
| **Nonces** | Alla AJAX-anrop skickar nonce (admin + frontend) |
| **Felhantering** | Alla AJAX-anrop har success/error eller .fail() |
| **CSS-klasser** | Använder `.mrt-*` enligt COMPONENT_LIBRARY |
| **XSS** | `escapeHtml()`, `.text()`, `textContent`, `document.createElement` |
| **Clean Code** | Funktioner under ~50 rader, uppdelade moduler |

---

## ✅ Genomförda förbättringar

| Kategori | Åtgärd |
|----------|--------|
| XSS | escapeHtml, showError med .text(), document.createElement för felmeddelanden |
| Felhantering | .fail() på alla AJAX, error-callbacks |
| i18n | saving, adding, timeHint, pickup, dropoff, edit, tripAdded, tripRemoved, saved |
| Clean Code | initRouteUI, initStopTimesUI, initTimetableServicesUI uppdelade |
| DRY | MRTAdminUtils.getAjaxUrl() överallt |
| Nonce | Frontend skickar mrtFrontend.nonce, backend verifierar wp_verify_nonce |

---

## Namnkonventioner

| Nuvarande | Kommentar |
|-----------|-----------|
| `MRTAdminUtils` | OK – objektnamn |
| `MRTAdminServiceEdit` | OK – modul/namespace |

---

## Filöversikt

| Fil | Rader | Ansvar |
|-----|-------|--------|
| admin.js | 29 | Entry point, MRTAdminServiceEdit.init |
| admin-utils.js | 83 | getAjaxUrl, escapeHtml, populateDestinationsSelect, setSelectState, validateTimeFormat |
| admin-service-edit.js | 306 | Route/destination, title preview, stoptimes-formulär |
| admin-route-ui.js | 166 | Lägg till/ta bort/ordna stationer, end stations |
| admin-stoptimes-ui.js | 232 | Legacy inline redigering av stoptimes |
| admin-timetable-services-ui.js | 234 | Lägg till/ta bort turer i tidtabell |
| frontend.js | 157 | Journey planner, månadskalender |

---

## Sammanfattning

- **Struktur och grund:** Bra – följer STYLE_GUIDE och COMPONENT_LIBRARY
- **Alla förbättringar genomförda** – inga återstående observationer
