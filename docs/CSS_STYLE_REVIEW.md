# CSS – Granskning mot Style Guide

Granskning av `assets/*.css` mot STYLE_GUIDE.md (sektion 3. CSS), DESIGN_SYSTEM.md och COMPONENT_LIBRARY.md.

**Senast analyserad:** 2025-02-28 (efter commit 29a523b)

---

## ✅ Följs

| Krav | Status |
|------|--------|
| **Prefix** | Alla egna klasser använder `.mrt-` |
| **BEM-liknande** | Block + modifier (`.mrt-btn--primary`), element (`.mrt-form-label__hint`) |
| **Variabler** | Design tokens med `--mrt-` i admin-base-tokens.css |
| **Inga inline styles** | All styling i CSS-filer |
| **Mobile-first** | `@media (min-width)` i admin-responsive.css |
| **Struktur** | Uppdelad i tokens, components, utilities, timetable, meta-boxes, dashboard, ui |

---

## Filöversikt

| Fil | Rader (ca) | Ansvar |
|-----|------------|--------|
| admin-base-tokens.css | ~120 | Färger, spacing, typografi, shadows, borders |
| admin-base-components.css | ~85 | Card, box, alert, section, grid |
| admin-base-utilities.css | ~80 | Margin, display, text, opacity utilities |
| admin-components-form.css | ~95 | Form, label, input, form-row |
| admin-components-ui.css | ~155 | Button, badge, heading, empty, card, code |
| admin-components-width.css | ~25 | mrt-w-40 till mrt-w-200 |
| admin-timetable-table.css | ~35 | Tabellkomponent |
| admin-timetable-month.css | ~80 | Månadskalender |
| admin-timetable-overview-layout.css | ~160 | Grid, route header, station columns |
| admin-timetable-overview-cells.css | ~120 | Time cells, transfer rows |
| admin-timetable-overview-components.css | ~95 | Service header, train type, special label |
| admin-meta-boxes.css | ~25 | Taxonomy hide, editing-from-timetable |
| admin-meta-boxes-fields.css | ~30 | Stoptimes-struktur |
| admin-meta-boxes-edit.css | ~70 | Inline-redigering |
| admin-dashboard.css | (loader) | guide, misc |
| admin-dashboard-guide.css | ~15 | Guide, info-box |
| admin-dashboard-misc.css | ~25 | Time error, misc |
| admin-ui.css | (loader) | status, journey |
| admin-ui-status.css | ~25 | Success/error message |
| admin-ui-journey.css | ~25 | Journey table |
| admin-responsive.css | ~170 | Media queries |

---

## ⚠️ Mindre observationer

### 1. WordPress-core selektor

**admin-base-utilities.css** (rad 77):
```css
.wrap h1 {
    color: var(--mrt-text-primary);
}
```
- `.wrap` är WordPress admin wrapper, inte `.mrt-`. Acceptabelt för admin-override av WP-struktur.

### 2. Hardkodade värden (låg prioritet)

| Fil | Värde | Kommentar |
|-----|-------|-----------|
| admin-base-utilities.css | `font-size: 2rem` (.mrt-text-2xl) | DESIGN_SYSTEM har max --mrt-font-xl (1.2rem). Överväg --mrt-font-2xl token. |
| admin-base-utilities.css | `minmax(250px, 1fr)` (.mrt-grid-auto-250) | 250px – kan tokeniseras som --mrt-grid-min. |
| admin-components-width.css | 40, 60, 80, 100, 150, 200px | Width-utilities – dokumenterade i COMPONENT_LIBRARY. OK. |
| admin-components-form.css | 100px, 150px, 300px | Input-widths – specifika för formulär. |
| admin-responsive.css | 60px, 50px, 120px | Breakpoint-specifika höjder – vanligt i responsive. |

### 3. Taxonomy-selektor

**admin-meta-boxes.css**:
```css
.taxonomy-mrt_train_type .term-description-wrap { display: none; }
```
- Målger WordPress taxonomy-admin. `mrt_train_type` är vår taxonomy – prefix OK.

---

## Sammanfattning

- **Struktur och grund:** Bra – följer STYLE_GUIDE, DESIGN_SYSTEM och COMPONENT_LIBRARY
- **Namnkonventioner:** Konsekvent `.mrt-` prefix, BEM-liknande modifier
- **Tokens:** Används konsekvent för färger, spacing, typografi
- **Inga kritiska avvikelser** – mindre observationer är acceptabla
