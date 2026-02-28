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

## ⚠️ Mindre observationer (valfria)

### 1. admin-stoptimes-ui.js – getAjaxUrl

- Använder `mrtAdmin.ajaxurl` direkt i $.post (4 ställen)
- **Åtgärd:** Lägg till `mrt-admin-utils` som dependency, använd `window.MRTAdminUtils.getAjaxUrl()` för konsekvens

### 2. admin-stoptimes-ui.js – cancelEditStopTime

- `mrt_get_stoptime` AJAX-anrop saknar `.fail()` – vid nätverksfel ingen feedback (rad 168)
- **Åtgärd:** Lägg till `.fail()` med t.ex. exitEditMode + alert

### 3. admin-service-edit.js – bindTimeValidation

- `errorText` sätts i HTML via `$field.append('<span>'+errorText+'</span>')` (rad 219)
- `errorText` kommer från mrtAdmin (översatt) – säker, men `document.createElement` + `textContent` skulle vara mer konsekvent

### 4. admin.js – MODULES-kommentar

- Listar inte `getAjaxUrl` under admin-utils
- **Åtgärd:** Uppdatera kommentaren

### 5. Frontend – nonce (valfritt)

- `mrt_search_journey`, `mrt_get_timetable_for_date` skickar ingen nonce
- För publika read-only anrop inte nödvändigt
- **Åtgärd:** Överväg nonce om policy kräver extra CSRF-skydd

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
- **Återstår:** Endast mindre, valfria förbättringar (getAjaxUrl i stoptimes, .fail på cancel, MODULES-kommentar)
