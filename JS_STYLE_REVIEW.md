# JavaScript – Granskning mot Style Guide

Granskning av `assets/*.js` mot STYLE_GUIDE.md och COMPONENT_LIBRARY.md.

**Senast analyserad:** 2025-02-17

---

## ✅ Följs

| Krav | Status |
|------|--------|
| **IIFE** | Alla filer wrappas i `(function($) { ... })(jQuery)` |
| **jQuery** | Använder `$` för DOM |
| **console.log** | Endast bakom `window.mrtDebug` (admin.js, admin-timetable-services-ui.js) |
| **camelCase** | Variabler och funktioner använder camelCase |
| **Prefix** | `mrtAdmin`, `mrtFrontend` för lokaliserade strängar |
| **Nonces** | Admin-AJAX skickar nonce; frontend read-only anrop (search, timetable) saknar – OK för publika läsoperationer |
| **Felhantering** | De flesta AJAX-anrop har success/error-hantering |
| **CSS-klasser** | Använder `.mrt-*` enligt COMPONENT_LIBRARY |
| **XSS** | `escapeHtml()` för användardata, `.text()` för meddelanden i showError |

---

## ✅ Genomförda förbättringar

- XSS: escapeHtml för station.name, stationName; showError använder .text()
- Felhantering: error-callback för route end stations AJAX
- i18n: saving, adding, timeHint, pickup, dropoff, edit, tripAdded, tripRemoved
- Clean Code: admin-service-edit, admin-timetable-services-ui uppdelade

---

## ⚠️ Återstående förbättringar

### 1. Långa funktioner ✓ (genomfört)

- **admin-route-ui.js:** `initRouteUI` delad i `bindAddStation`, `bindMoveAndRemove`, `bindEndStationsChange`
- **admin-stoptimes-ui.js:** `initStopTimesUI` delad i `bindRowClick`, `bindSave`, `bindAdd`, `bindCancel`, `bindDelete`

### 2. Felhantering ✓ (genomfört)

- **admin-stoptimes-ui.js:** `.fail()` tillagd på alla tre AJAX-anrop (save, add, delete)

### 3. XSS – frontend.js ✓ (genomfört)

- **frontend.js:** `showError()` används nu för initMonthCalendar (response.data.message via .text())

### 4. DRY – getAjaxUrl

- **admin-service-edit.js**, **admin-route-ui.js**, **admin-timetable-services-ui.js**: Samma URL-logik upprepas
- **Åtgärd:** Lägg till `MRTAdminUtils.getAjaxUrl()` i admin-utils.js och använd överallt

### 5. i18n – hårdkodade strängar

| Fil | Sträng | Åtgärd |
|-----|--------|--------|
| **admin-route-ui.js** | `"✓ Saved"` (rad 129) | Lägg till `mrtAdmin.saved` |
| **admin-service-edit.js** | `bindTimeValidation` error span (rad 219) | `errorText` kommer redan från mrtAdmin – OK |

### 6. Frontend – nonce för AJAX (valfritt)

- `mrt_search_journey`, `mrt_get_timetable_for_date` skickar ingen nonce
- För publika read-only anrop är nonce inte strikt nödvändig
- **Åtgärd:** Överväg nonce för extra CSRF-skydd om policy kräver det

---

## Namnkonventioner

| Nuvarande | Style guide | Kommentar |
|-----------|-------------|-----------|
| `MRTAdminUtils` | mrtAdmin, mrtFrontend | OK – objektnamn, inte lokaliserings-objekt |
| `MRTAdminServiceEdit` | – | OK – modul/namespace |

---

## Filöversikt

| Fil | Rader | Ansvar |
|-----|-------|--------|
| admin.js | 29 | Entry point, laddar MRTAdminServiceEdit |
| admin-utils.js | 75 | escapeHtml, populateDestinationsSelect, setSelectState, validateTimeFormat |
| admin-service-edit.js | 305 | Route/destination, title preview, stoptimes-formulär |
| admin-route-ui.js | 157 | Lägg till/ta bort/ordna stationer på rutt, end stations |
| admin-stoptimes-ui.js | 205 | Legacy inline redigering av stoptimes |
| admin-timetable-services-ui.js | 235 | Lägg till/ta bort turer i tidtabell |
| frontend.js | 155 | Journey planner, månadskalender |

---

## Sammanfattning

- **Struktur och grund:** Bra
- **Prioritet 1–3 genomförda:** Felhantering (.fail), XSS frontend, refaktorering initRouteUI/initStopTimesUI
- **Återstår:** DRY getAjaxUrl, i18n "✓ Saved"
