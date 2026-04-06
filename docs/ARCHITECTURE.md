# Arkitektur: ansvar, testning, affärslogik vs UI

Kort riktlinje för **Museum Railway Timetable** så att ansvar fördelas tydligt, kod kan testas, och **affärskritisk logik** inte låses in i presentation.

**Relaterat:** [PUBLIC_JOURNEY_IMPLEMENTATION_PLAN.md](PUBLIC_JOURNEY_IMPLEMENTATION_PLAN.md) (reseflöde funktioner → vyer), [STYLE_GUIDE.md](STYLE_GUIDE.md), [WCAG_JOURNEY_WIZARD.md](WCAG_JOURNEY_WIZARD.md) (tillgänglighet wizard). **Pull requests:** checklista i [`.github/pull_request_template.md`](../.github/pull_request_template.md).

---

## 1. Ansvarsfördelning (lager)

| Lager | Roll | Exempel |
|--------|------|---------|
| **Domän / affärslogik** | Regler oberoende av WordPress och UI | Prismatris, datum/tid-validering, normalisering av connections, kalenderstatus per dag |
| **Applikation / WP-adapter** | I/O: `$wpdb`, `get_option`, AJAX som validerar och delegerar | `MRT_ajax_*`, tunn mappning från `$_POST` till parametrar |
| **Presentation** | Shortcode/HTML; JS för tillstånd och anrop | `shortcode-*.php`, `assets/*-wizard.js` (inga dolda affärsregler utöver visning) |

**Vid ändringar:** Om en funktion kan beskrivas och testas utan `echo` och utan `$_POST` ska den ligga i **`inc/functions/`** (prefix `MRT_*`), inte i template-strängar.

---

## 2. Testning

- **Enhetstester (PHPUnit):** Ren PHP i `tests/` mot produktionskod i `inc/`; se `phpunit.xml.dist` och `composer test`.
- **Utökning:** Ny eller ändrad affärsregel bör följa med test i samma leverans, när logiken är ren nog.
- **Integration:** Väljs när domänlogik måste verifieras mot databas eller full WordPress; då WP test suite eller riktade tester – inte ersättning för enhetstest av rena funktioner.
- **CI:** Pipeline kör `composer test` tillsammans med befintlig validering (se `.github/workflows/ci.yml`).
- **Refaktor:** Flytta validering som inte behöver `$_POST` till namngivna `MRT_*`-funktioner (t.ex. `MRT_journey_validate_station_pair_ids` i `journey-parse.php`) så samma regler kan testas utan HTTP.

---

## 3. Affärskritisk kod och UI

- **PHP:** Shortcode och AJAX ska **samla input → anropa domänfunktion → rendera**; undvik långa grenar av affärslogik inline i HTML.
- **JavaScript:** Servern är sanning för sökning, priser och giltiga datum; klienten visar svar och fel. Duplicera inte samma regler i JS.
- **Gemensam regel** (admin + publikt): **en** implementation i PHP, inte copy-paste mellan lager.

**Checklista för ny funktion:** (1) Logik i `inc/functions/…` (2) Tester i `tests/Unit/` där det går (3) Tunnt lager i shortcode/AJAX (4) UI endast visar och skickar parametrar.

---

## 4. Varför det spelar roll

Lös koppling mellan domän och UI gör det möjligt att byta tema, shortcode-layout eller API-format utan att röra kärnreglerna, och tvärtom att testa regler utan webbläsare.
