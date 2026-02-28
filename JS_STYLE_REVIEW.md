# JavaScript – Granskning mot Style Guide

Granskning av `assets/*.js` mot STYLE_GUIDE.md och COMPONENT_LIBRARY.md.

**Senast åtgärdat:** XSS-fix, felhantering, i18n, Clean Code-refaktorering genomförd.

---

## ✅ Följs

| Krav | Status |
|------|--------|
| **IIFE** | Alla filer wrappas i `(function($) { ... })(jQuery)` |
| **jQuery** | Använder `$` för DOM |
| **console.log** | Endast bakom `window.mrtDebug` (admin.js, admin-timetable-services-ui.js) |
| **camelCase** | Variabler och funktioner använder camelCase |
| **Prefix** | `mrtAdmin`, `mrtFrontend` för lokaliserade strängar |
| **Nonces** | Alla AJAX-anrop skickar nonce |
| **Felhantering** | De flesta AJAX-anrop har success/error-hantering |
| **CSS-klasser** | Använder `.mrt-*` enligt COMPONENT_LIBRARY |

---

## ✅ Förbättringar (genomförda)

### 1. XSS-säkerhet ✓
- `admin-service-edit.js`, `admin-route-ui.js`: `escapeHtml()` för station.name, stationName
- **frontend.js**: `showError()` använder `.text(message)` istället för `.html()`

### 2. Felhantering ✓
- `admin-route-ui.js`: error-callback för route end stations AJAX

### 3. i18n ✓
- `admin-service-edit.js`: saving, timeHint, pickup, dropoff
- `admin-timetable-services-ui.js`: edit, tripAdded, tripRemoved
- `admin-stoptimes-ui.js`: saving, adding

### 4. showError – XSS ✓
- `frontend.js`: `.text(message)` för säker insättning

### 5. Clean Code – långa funktioner ✓
- `admin-service-edit.js`: validateStoptimeFormats, sendSaveAllStoptimes
- `admin-stoptimes-ui.js`: exitEditMode, applyStoptimeToRow
- `admin-timetable-services-ui.js`: bindRouteChange, bindAddService, bindRemoveService, helpers

---

## Namnkonventioner

| Nuvarande | Style guide | Kommentar |
|-----------|--------------|-----------|
| `MRTAdminUtils` | mrtAdmin, mrtFrontend | OK – objektnamn, inte lokaliserings-objekt |
| `MRTAdminServiceEdit` | – | OK – modul/namespace |

---

## Sammanfattning

- **Struktur och grund:** Bra
- Alla förbättringar genomförda (XSS, felhantering, i18n, Clean Code)
