# JavaScript – Granskning mot Style Guide

Granskning av `assets/*.js` mot STYLE_GUIDE.md och COMPONENT_LIBRARY.md.

**Senast analyserad:** 2025-02-17 (efter commit 68ff30a)

---

## ✅ Följs

| Krav | Status |
|------|--------|
| **IIFE** | Alla filer wrappas i `(function($) { ... })(jQuery)` |
| **jQuery** | Använder `$` för DOM |
| **console.log** | Endast bakom `window.mrtDebug` (admin.js, admin-timetable-services-ui.js) |
| **camelCase** | Variabler och funktioner använder camelCase |
| **Prefix** | `mrtAdmin`, `mrtFrontend` för lokaliserade strängar |
| **Nonces** | Admin-AJAX skickar nonce; frontend read-only anrop saknar – OK för publika läsoperationer |
| **Felhantering** | Alla AJAX-anrop har success/error eller .fail() |
| **CSS-klasser** | Använder `.mrt-*` enligt COMPONENT_LIBRARY |
| **XSS** | `escapeHtml()` för användardata, `.text()` för meddelanden |
| **Clean Code** | Funktioner under ~50 rader, uppdelade moduler |

---

## ✅ Genomförda förbättringar

- XSS: escapeHtml, showError med .text()
- Felhantering: .fail() på alla AJAX, error-callbacks
- i18n: saving, adding, timeHint, pickup, dropoff, edit, tripAdded, tripRemoved, saved
- Clean Code: initRouteUI, initStopTimesUI, initTimetableServicesUI uppdelade
- DRY: MRTAdminUtils.getAjaxUrl() i admin-service-edit, admin-route-ui, admin-timetable-services-ui

---

## ✅ Mindre observationer (genomförda)

### 1. admin-stoptimes-ui.js – getAjaxUrl ✓
- `mrt-admin-utils` som dependency, använder `window.MRTAdminUtils.getAjaxUrl()`

### 2. admin-stoptimes-ui.js – cancelEditStopTime ✓
- `.fail()` tillagd på `mrt_get_stoptime`

### 3. admin-service-edit.js – bindTimeValidation ✓
- `document.createElement` + `textContent` för felmeddelande

### 4. admin.js – MODULES-kommentar ✓
- Uppdaterad med getAjaxUrl, escapeHtml

### 5. Frontend – nonce ✓
- `mrtFrontend.nonce` tillagd, skickas med mrt_search_journey och mrt_get_timetable_for_date
- Backend verifierar med wp_verify_nonce

---

## Namnkonventioner

| Nuvarande | Style guide | Kommentar |
|-----------|-------------|-----------|
| `MRTAdminUtils` | mrtAdmin, mrtFrontend | OK – objektnamn |
| `MRTAdminServiceEdit` | – | OK – modul/namespace |

---

## Filöversikt

| Fil | Rader | Ansvar |
|-----|-------|--------|
| admin.js | 29 | Entry point, MRTAdminServiceEdit.init |
| admin-utils.js | 83 | getAjaxUrl, escapeHtml, populateDestinationsSelect, setSelectState, validateTimeFormat |
| admin-service-edit.js | 305 | Route/destination, title preview, stoptimes-formulär |
| admin-route-ui.js | 166 | Lägg till/ta bort/ordna stationer, end stations |
| admin-stoptimes-ui.js | 228 | Legacy inline redigering av stoptimes |
| admin-timetable-services-ui.js | 234 | Lägg till/ta bort turer i tidtabell |
| frontend.js | 155 | Journey planner, månadskalender |

---

## Sammanfattning

- **Struktur och grund:** Bra – följer STYLE_GUIDE och COMPONENT_LIBRARY
- **Alla prioriterade förbättringar genomförda**
- **Alla observationer genomförda**
