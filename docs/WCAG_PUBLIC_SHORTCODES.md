# WCAG – publika shortcodes (resa, månad, översikt)

Mål: **WCAG 2.1 nivå AA** där det är tekniskt rimligt utan att duplicera temats ansvar. Detta dokument täcker **`[museum_journey_planner]`**, **`[museum_timetable_month]`** och **`[museum_timetable_overview]`** (PHP, tidtabellsrutnät, `assets/frontend.js`, CSS).

**Relaterat:** [WCAG_JOURNEY_WIZARD.md](WCAG_JOURNEY_WIZARD.md), [SHORTCODES_OVERVIEW.md](SHORTCODES_OVERVIEW.md), [ARCHITECTURE.md](ARCHITECTURE.md), [RELEASE_A11Y_SMOKE.md](RELEASE_A11Y_SMOKE.md).

---

## 1. `[museum_journey_planner]`

| Område | Åtgärd |
|--------|--------|
| **Landmärken** | Yttre omslutning `role="region"` med `aria-label` (Journey planner). Resultat: `role="region"`, `aria-live="polite"`, `aria-relevant="additions text"`, `aria-busy` styrs under AJAX-sökning. |
| **Tabell** | Delad markup i `inc/functions/journey-connections-table.php`: `<caption>`, `scope="col"`, tider formaterade konsekvent. Retursökning (AJAX): särskild caption-text (`Return train connections…`). |
| **Rubrik** | `h3` med `id="mrt-journey-results-heading"` för fokus efter sök; JS flyttar fokus och tar bort tillfällig `tabindex` vid blur. |
| **Meddelanden** | `MRT_render_alert`: `role="alert"` för fel/varning, `role="status"` för info. Inline alert-rutor i resultat samma mönster. |
| **JS** | `aria-busy` på sökknapp och resultatregion; fel från nät/API behandlas likadant för fokus. |

**Filer:** `inc/shortcodes/shortcode-journey.php`, `inc/admin-ajax/journey-render.php`, `inc/functions/journey-connections-table.php`, `assets/frontend.js`, `assets/admin-timetable-table.css` (caption-stil).

---

## 2. `[museum_timetable_month]`

| Område | Åtgärd |
|--------|--------|
| **Landmärken** | Kalenderwrapper `role="region"` med beskrivande `aria-label` (månad + år). Panel under kalendern för vald dags tidtabell: `role="region"`, `aria-live="polite"`, `aria-busy`, `tabindex="-1"`, `aria-label`; fokus efter AJAX-laddning. |
| **Navigation** | `role="navigation"` med `aria-label` (Month navigation). |
| **Tabell** | `<caption>` (Operating days for …), veckodagar `scope="col"`. |
| **Dagar med trafik** | `<button type="button">` med `aria-label` (datum + ev. antal turer), `aria-pressed` uppdateras i JS; visuella siffror/markör `aria-hidden` där etiketten är fullständig. |
| **Dagar utan trafik** | Cell med datum siffra, ingen knapp. |
| **Legend** | Dekorativ färgpunkt `aria-hidden="true"`. |
| **Rörelse / fokus** | `prefers-reduced-motion`: ingen scale-hover på dagknappar; `:focus-visible` på knappar; slideDown för panel kan kringgås vid reduced motion i JS. |

**Filer:** `inc/shortcodes/shortcode-month.php`, `assets/frontend.js`, `assets/admin-timetable-month.css`.

---

## 3. `[museum_timetable_overview]` (och samma rutnät i dag-vy)

Layouten är ett **CSS Grid** av `div`-element (inte `<table>`). För att kompensera används:

| Område | Åtgärd |
|--------|--------|
| **Översikt** | Yttre `mrt-timetable-overview`: `role="region"`, `aria-label` med tidtabellens titel (`Timetable overview: %s`). |
| **Dagvy (AJAX)** | `mrt-day-timetable`: `role="region"`, `aria-labelledby` mot `h3` med unikt id (datum i rubriken). |
| **Ruttblock** | Ruttitel är **`h3`** med unikt id; `mrt-overview-grid` har `role="group"`, `aria-labelledby` mot samma id. |
| **Tidsceller** | `aria-label` byggs med `MRT_overview_grid_cell_aria_label` (station rad + tåg/tjänst + tid). Tågbyte-rader inkluderade. |
| **Dekorativt** | Separatorer mellan block `aria-hidden="true"`; pil mellan Från/Till `aria-hidden="true"`; tom header-cell `aria-hidden="true"`. |
| **Rörelse** | `prefers-reduced-motion: reduce` tar bort hover-skuggeffekt på rutt-kort. |

**Filer:** `inc/functions/timetable-view/overview.php`, `grid.php`, `grid-rows.php`, `grid-helpers.php`, `assets/admin-timetable-overview-layout.css`.

**Admin:** Förhandsvisning i metabox använder samma HTML; landmärken är fortfarande meningsfulla i admin.

---

## 4. Manuell checklista (vid release eller temaändring)

Se den samlade listan i **[RELEASE_A11Y_SMOKE.md](RELEASE_A11Y_SMOKE.md)** (wizard + alla tre shortcodes + admin-fokus).

---

## 5. Kända gränser

- **Full WCAG** kräver sidvis granskning (kontrast mot tema).
- **CSS Grid** med `display: contents` ger inte tabellsemantik; vi litar på **region + rubriker + aria-label** på celler.
- **Tredjepartstema** kan åsidosätta plugin-CSS.

---

## 6. Referenser

- [WCAG 2.1](https://www.w3.org/TR/WCAG21/)
- [WAI-ARIA Practices](https://www.w3.org/WAI/ARIA/apg/)
