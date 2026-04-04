# Plan: publikt reseflöde – funktioner först, sedan vyer

Princip: **domänlogik och testbara PHP-funktioner** byggs och stabiliseras först; **HTML/CSS/JS (vyer)** kopplas sedan till samma API:er. Det minskar risk att UI låser in fel datamodell.

**Underlag:** [USER_STORIES_DATA_GAP_ANALYSIS.md](USER_STORIES_DATA_GAP_ANALYSIS.md), [mockup-analys-funktionella-krav-user-stories.md](mockup-analys-funktionella-krav-user-stories.md), [UI_MOCKUP_PLAN.md](UI_MOCKUP_PLAN.md), mockups i `docs/mockups/`.

---

## 0. Scope-beslut innan kod (kort)

| Fråga | Påverkar |
|--------|----------|
| Ska v1 stödja **byte mellan tjänster** eller bara direkta tåg? | Algoritmkomplexitet, tidslinje-UI |
| **Priser** i v1 eller senare fas? | Egen datamodell + vy |
| **Trafikmeddelanden** per tjänst – MVP med post meta? | Ett fält räcker första version |

Dokumentera besluten här eller i `FUTURE_WORK.md` när de är tagna.

---

## Del 1 – Funktioner (backend / `inc/`)

**Status:** Implementerad i `inc/functions/journey-loader.php` och inkluderade moduler (`journey-*.php`), samt utökningar i `helpers-datetime.php`, `helpers-services.php`, `services.php`. Admin: prismatris under inställningssidan, fältet **Public notice** på resa (service).

Mål: tydliga `MRT_*`-funktioner som shortcode, AJAX och framtida REST kan anropa utan duplicerad SQL i templates.

### Fas 1.1 – Utöka befintlig resdata (ingen ny skärm än)

| Leverans | Beskrivning |
|----------|-------------|
| **`MRT_get_connection_journey_detail($service_id, $from_station_id, $to_station_id)`** | Returnerar ordnad lista stopp **mellan** från och till (tider, stationstitlar, sekvens), total restid om räknbar, flaggor pickup/dropoff. Bygger på `MRT_get_service_stop_times` + SQL/ordning. |
| **`MRT_format_duration_minutes($dep, $arr)`** | Hjälpare HH:MM → minuter (återanvänd i tabeller och API). |

**Acceptans:** Enhetstest eller manuell fixture med känd service/stationer ger förväntad tidlinje.

---

### Fas 1.2 – Kalenderunderlag för sträcka

| Leverans | Beskrivning |
|----------|-------------|
| **`MRT_get_journey_calendar_month($from_id, $to_id, $year, $month)`** | För varje dag i månaden: t.ex. `'none' \| 'traffic_no_match' \| 'ok'` – motsvarar mockupens två gröna nyanser + inget val. Implementera med **begränsat antal queries** (t.ex. batcha tjänster per datum eller en månadsvise loop med cache i samma request). |
| **`MRT_get_all_traffic_dates_in_range($from_ymd, $to_ymd)`** | Optional: alla datum där *någon* tjänst går (för legend “trafik men ej vald resa”). Kan återanvända tidtabellsmeta. |

**Acceptans:** För kända fixture-data stämmer cellstatus med manuellt räknade `MRT_find_connections`.

---

### Fas 1.3 – Tur / retur (affärslogik)

| Leverans | Beskrivning |
|----------|-------------|
| **`MRT_find_connections` behålls** för utresa som idag (direkt). |
| **`MRT_find_return_connections($from_station_id, $to_station_id, $dateYmd, $outbound_arrival_hhmm, $min_turnaround_minutes = 0)`** | Söker `to → from` samma datum där avgång från `to` är **efter** ankomst + marginal. Returnerar samma struktur som utresa för enhetlig rendering. |

**Acceptans:** Med testdata där retur finns efter ankomst returneras den; om ingen finns, tom lista med tydlig signal till UI.

---

### Fas 1.4 – Byte mellan tjänster (valfri / fas 2 beroende på scope)

| Leverans | Beskrivning |
|----------|-------------|
| **`MRT_find_multi_leg_connections(...)`** | Antingen: (a) begränsad **2-leg** sök (A→B på tåg 1, byte på station X, B→C på tåg 2) med max antal byten och minsta bytestid, eller (b) manuellt definierade “sammansatta resor”. |
| Återanvänd **`MRT_find_connecting_services`** som byggsten där det passar. |

**Acceptans:** Minst ett scenario från mockup (byte + väntetid) kan representeras i retur-array med segmentlista.

---

### Fas 1.5 – Priser (om scope)

| Leverans | Beskrivning |
|----------|-------------|
| Ny lagring | T.ex. `mrt_price_matrix` option eller CPT `mrt_price_table` med JSON/struktur: biljetttyp × åldersgrupp → belopp + ev. giltighetsdatum. |
| **`MRT_get_prices_for_context($args)`** | Returnerar matris för visning kopplad till enkel/tur-retur/heldag (mockupens tabell). |

**Acceptans:** Priser kan ändras i admin utan kodändring.

---

### Fas 1.6 – Trafikmeddelanden (MVP)

| Leverans | Beskrivning |
|----------|-------------|
| Post meta **`mrt_service_notice`** (textarea) eller strukturerad lista | Visas i anslutning till service i API-svar. |
| **`MRT_get_service_notice($service_id, $dateYmd)`** | Enkel wrapper; framtida datumstyrd notice kan bygga på samma meta med JSON. |

**Acceptans:** Journey-detail inkluderar `notice` när satt.

---

### Fas 1.7 – Enhetligt sökresultat-objekt

| Leverans | Beskrivning |
|----------|-------------|
| **`MRT_normalize_connection_for_api($row)`** | Ett associativt schema: `service_id`, `departure`, `arrival`, `duration_minutes`, `train_type`, `segments` (vid flerben), `notice`, `legs[]` med fordon per delsträcka om tillgängligt. |

Alla nya funktioner konsumerar/producerar detta format så **AJAX/REST** kan serialisera till JSON utan vy-specifik logik.

---

## Del 2 – API-yta (mellan funktioner och vy)

Kort kedja innan pixelarbete:

1. **Utöka `MRT_ajax_search_journey`** (eller ny action) med parametrar: `trip_type` (single|return), ev. `outbound_service_id` + `outbound_arrival` för retur-steg.
2. **Ny AJAX** (eller samma med `step`): `mrt_calendar_month` → JSON från `MRT_get_journey_calendar_month`.
3. **Nonce + rate limiting** enligt befintlig `mrt_frontend`-mönster.

Ingen ny skärm krävs för att **testa** – Postman eller en minimal admin-debug-sida räcker.

---

## Del 3 – Vyer (i ordning enligt mockup-flöde)

Bygg **efter** att Del 1.1–1.3 (minst) och API i Del 2 finns för motsvarande steg.

| Ordning | Mockup / vy | Koppling till funktioner |
|--------|-------------|---------------------------|
| **V1** | Hero + sök (`sok-din-resa`) | befintlig stationlista + validering; lägg till **Enkel / Tur–retur** (endast UI-state tills retur-API anropas). |
| **V2** | Välj datum (`valj-datum`) | `MRT_get_journey_calendar_month` + legend. |
| **V3** | Välj utresa (`valj-utresa`) | `MRT_find_connections` + `MRT_get_connection_journey_detail` för accordion; visa `train_type`, `notice`; pris-tabell om Fas 1.5 klar. |
| **V4** | Välj återresa (`valj-aterresa`) | Endast om tur–retur; `MRT_find_return_connections` + sammanfattningskort för vald utresa (data från föregående steg). |
| **V5** | Bekräftelse / nästa steg (biljett extern) | Kan vara statisk “gå till biljett”-länk i v1. |

**Tekniskt:** antingen utöka `[museum_journey_planner]` med steg-parameter och JS-router, eller ny shortcode `[museum_journey_wizard]` som laddar en liten modul (t.ex. `assets/journey-wizard.js`) – följ befintlig `MRT_`-prefix och textdomain `museum-railway-timetable`.

**Stil:** återanvänd tokens från [UI_MOCKUP_PLAN.md](UI_MOCKUP_PLAN.md); bygg CSS i `assets/` med BEM-liknande klasser som redan används (`mrt-*`).

---

## Del 4 – Kvalitet

- **Tester:** PHPUnit för rena funktioner (kalender, returfilter, duration) där WordPress kan bootstrappas; annars integrationstester via WP test suite när det finns.
- **Tillgänglighet:** kalender och accordion med tangentbord och ARIA (krav från user stories + WCAG).
- **Översättning:** alla nya strängar via `__()` / `esc_html__()` med textdomain `museum-railway-timetable`.

---

## Översikt: ordning i tid

```
[Scope-beslut]
     ↓
1.1 Journey detail + duration
     ↓
1.2 Kalendermånad
     ↓
1.3 Retur-anslutningar
     ↓
(API: AJAX för sök + kalender + detalj)
     ↓
Vyer V1 → V2 → V3 → V4
     ↓
(1.4 Multi-leg om scope)
     ↓
(1.5 Priser om scope)
     ↓
1.6 Notiser + polish
```

---

**Relaterade dokument:** [USER_STORIES_DATA_GAP_ANALYSIS.md](USER_STORIES_DATA_GAP_ANALYSIS.md) · [UI_MOCKUP_PLAN.md](UI_MOCKUP_PLAN.md)
