# User stories vs datamodell och metoder – gap-analys

Jämför kraven i [mockup-analys-funktionella-krav-user-stories.md](mockup-analys-funktionella-krav-user-stories.md) med pluginets faktiska data ([DATA_MODEL.md](DATA_MODEL.md)) och PHP-funktioner.

**Föreslagen implementationsordning (funktioner → vyer):** [PUBLIC_JOURNEY_IMPLEMENTATION_PLAN.md](PUBLIC_JOURNEY_IMPLEMENTATION_PLAN.md). Syftet är att se **var vi redan har stöd**, **var det räcker delvis**, och **var ny struktur eller logik krävs**.

> **Uppdatering 2026-04:** MVP enligt planen (tur/retur, kalender per sträcka, AJAX, wizard, prismatris, notis per tjänst, enkel planner) är **levererad** – se tabellen *Leveransstatus* i [PUBLIC_JOURNEY_IMPLEMENTATION_PLAN.md](PUBLIC_JOURNEY_IMPLEMENTATION_PLAN.md). Avsnitt **1–6 nedan** är en **djupare krav- och mockup-jämförelse**; enskilda rader kan fortfarande beskriva **nice-to-have** eller skillnad mot full mockup (t.ex. flerben i UI, autocomplete).

**Kärnfunktioner i kodbasen (referens):**

- `MRT_find_connections()` – hittar resor där **samma tåg** (en `mrt_service`) stannar vid både från- och till-station i rätt ordning (`inc/functions/services.php`).
- `MRT_services_running_on_date()` – alla tjänster som har tidtabell med valt datum (`inc/functions/services.php`).
- `MRT_get_timetables_for_date()` – tidtabeller som innehåller ett datum.
- `MRT_get_service_stop_times()`, `MRT_get_service_destination()`, `MRT_get_service_train_type_for_date()` – stopp, mål, fordonstyp (inkl. datumoverride via meta `mrt_service_train_types_by_date`).
- `MRT_find_connecting_services()` – **nästa** avgångar från en station efter en ankomsttid (för visning av anslutning), men **kopplas inte** in i `MRT_find_connections()` som flerstegsresa.
- Shortcode `[museum_journey_planner]` + `MRT_ajax_search_journey` – från/till/datum, validering som i kraven (olika stationer, giltigt datum).

---

## Sammanfattning (2026-04, efter MVP-leverans)

| Område | Bedömning |
|--------|-----------|
| Stationer, rutter, stopp, tider | **Stark grund** – CPT + `mrt_stoptimes` |
| Enkel sökning från/till/datum | **Stöd** – `[museum_journey_planner]` + `MRT_ajax_search_journey` |
| Tur/retur som affärslogik | **Stöd** – `MRT_find_return_connections`, wizard + AJAX `trip_type=return` |
| Kalender med två nivåer (linje vs generell trafik) | **Stöd** – `MRT_get_journey_calendar_month` + `mrt_journey_calendar_month` |
| Resa med **byte mellan tjänster** | **Delvis** – `MRT_find_multi_leg_connections` finns; **inte** huvudflöde i wizard |
| Delsträckor, tidlinje, total restid | **Delvis** – `MRT_get_connection_journey_detail`, wizard accordion; full mockup-detalj kan skilja |
| Trafikslag per **delsträcka** | **Delvis** – per tjänst/datum; segmentvis mockup kan skilja |
| Priser (kategori × biljetttyp) | **Stöd (admin)** – `mrt_price_matrix`, `MRT_get_prices_for_context`, visning i wizard |
| Trafikmeddelanden per avgång | **Stöd** – `mrt_service_notice`, `MRT_get_service_notice` |
| Behovsuppehåll | **Delvis** – stoppflaggor + tidsvisning; dedikerad “P”-copy i API kan utökas |
| Tillstånd i flöde (steg utan att tappa val) | **UI** – wizard state i JS; backend stateless |

---

## 1. Resesökning (krav 1.1, stories 2.1)

| Krav | Stöd i plugin |
|------|----------------|
| Enkel / tur och retur | **Saknas.** Journey tar inte emot biljetttyp; ingen returquery. |
| Från / Till | **Stöd.** Stationer via `MRT_get_all_stations()`; validering i AJAX och resultat. |
| Validering fält ifyllda, olika stationer | **Stöd.** `MRT_ajax_search_journey`, `MRT_render_journey_results`. |
| Sök-knapp | **Stöd.** |
| Autocomplete, byt plats, tydliga fel | **Delvis.** Felmeddelanden finns; autocomplete och “swap” är UI-lager, data finns. |

---

## 2. Datumval (krav 1.2, story 2.2)

| Krav | Stöd i plugin |
|------|----------------|
| Kalender med tillgängliga datum | **Delvis.** Datum finns per tidtabell (`mrt_timetable_dates`). Ingen färdig funktion som returnerar **per månad** status för en viss sträcka. Kan byggas genom att för varje datum anropa `MRT_find_connections($from, $to, $date)` och/eller `MRT_services_running_on_date($date)` – men det är **O(dagar × queries)** utan optimering. |
| Markera “resa trafikeras” vs “trafik men ej denna sträcka” | **Delvis.** Logiken finns konceptuellt: `MRT_services_running_on_date` vs tomma `MRT_find_connections` – men ingen enhetlig **enum per datumcell** i ett API. |
| Månadsnavigation | **UI** – backend beroende av ovan. |
| Spärra ogiltiga datum | **Möjligt** med samma beräkning som ovan. |
| Säsong / specialdagar | **Implicit** – det som importeras/läggs in i tidtabellsdatum är sanningen; ingen separat “kalenderregel”-entitet. |

---

## 3. Val av utresa (krav 1.3, stories 2.3–2.5)

| Krav | Stöd i plugin |
|------|----------------|
| Lista avgångar för sträcka + datum | **Stöd** för **direkta** tåg: `MRT_find_connections`. |
| Avgång, ankomst, restid | **Delvis.** Från/till-tider i anslutningsraden; **total restid** beräknas inte i `MRT_map_connection_rows_to_result` men kan härledas från HH:MM om båda finns. |
| Direkt vs byte | **Aviker.** Mockup visar **byte** mellan olika fordon/tjänster. `MRT_find_connections` kräver **en** `service_post_id` för båda stopp – **inga flerstegsresor** i resultatet. `MRT_find_connecting_services` är byggt för annat syfte (nästa tåg efter ankomst), inte som planerare som returnerar sammansatta resor. |
| Trafikslag (ång/diesel/rälsbuss) | **Delvis.** `MRT_get_service_train_type_for_date` ger **en** typ per tjänst för visat datum. Flera typer på samma tjänst (taxonomy) hanteras i praktiken som första term; **segmentvis** typ som i mockup finns inte. |
| Välja utresa, expandera detalj | **Delvis.** Valt steg är UI; backend returnerar platt tabell utan tidlinje/pris. **Alla stopp** för en tjänst: `MRT_get_service_stop_times` (+ ordning `stop_sequence`). |
| Delsträckor, byte, väntetid, behovsuppehåll | **Delvis.** Stopp och tider: ja. **Byte mellan services:** nej i samma resa-object. **Behovsuppehåll:** `pickup_allowed`/`dropoff_allowed` – kan tolkas som “kan inte kliva på/av” men ingen dedikerad text/API som mockup. |

---

## 4. Val av återresa (krav 1.4, story 2.6)

| Krav | Stöd i plugin |
|------|----------------|
| Sammanfattning av vald utresa | **Saknas** som entitet – ingen sparad “bokning” eller resa-ID. |
| Återresor filtrerade mot utresa | **Saknas.** Användaren kan manuellt söka `to → from` samma datum; ingen regel för minsta tid mellan ankomst och returavgång eller “rimliga” returer. |
| Samma detaljvy som utresa | **Delvis** – samma `MRT_find_connections` om data finns åt andra hållet (beror på hur tjänster och rutter modelleras). |

Affärslogik (minsta bytestid, vistelsetid, samma dag) – **ingen** motsvarighet i kod.

---

## 5. Prisvisning (krav 1.5, story 2.7)

| Krav | Stöd i plugin |
|------|----------------|
| Pris per kategori och biljetttyp | **Saknas** – inga fält i datamodellen för prislista eller koppling service → pris. |

---

## 6. Trafikmeddelanden (krav 1.6, story 2.8)

| Krav | Stöd i plugin |
|------|----------------|
| Meddelande per avgång (ersatt lok, brandrisk, etc.) | **Saknas.** Global inställning `mrt_settings['note']` är **inte** kopplad till `mrt_service`. |
| Behovsuppehåll som egen notis | **Delvis** – kan härledas ur stoppflaggor + copy i UI; ingen standardiserad struktur. |

---

## 7. Expandera/dölja (krav 1.7)

| Krav | Stöd i plugin |
|------|----------------|
| Expanderbara kort, passerade stationer | **UI.** Data för full tidlinje per tjänst finns (`mrt_stoptimes` sorterad på `stop_sequence`). |

---

## 8. Tillstånd och navigering (krav 1.8, story 2.9)

| Krav | Stöd i plugin |
|------|----------------|
| Bevara val (biljettmodell, från/till, datum, utresa) | **Frontend/session eller URL-state.** WordPress-pluginet exponerar inget gemensamt “booking state”-API. GET-parametrar i journey (`mrt_from`, `mrt_to`, `mrt_date`) täcker delvis. |

---

## 9. Datatyper i krav 1.9 – snabbkoll

| Datatyp (kravdokument) | Plugin |
|------------------------|--------|
| Stationer / hållplatser | `mrt_station` |
| Relationer mellan stationer | `mrt_route` (`mrt_route_stations`) + faktiska stopp per tjänst |
| Avgångar per datum | Tidtabell → tjänster (`MRT_services_running_on_date`) |
| Trafikslag | Taxonomi + ev. `mrt_service_train_types_by_date` |
| Delsträckor, bytespunkter | Stopp på **en** tjänst; **byte mellan tjänster** som en resa: nej |
| Prislistor | Saknas |
| Trafikmeddelanden | Saknas (per service) |
| Regler behovsuppehåll | Delvis via stoppflaggor |
| Kalenderlogik trafikdagar | Via tidtabellsdatum; aggregering per sträcka måste byggas |

---

## 10. Rekommenderade riktningar (kort)

1. **Flerstegsresor:** Antingen utöka sök med graft-algoritm (noder = stationer vid tid T, kanter = tjänster) med begränsningar, eller manuellt kuraterade “sammansatta resor” – beslut om scope.
2. **Kalender-API:** En funktion typ `MRT_get_date_states_for_journey($from, $to, $year, $month)` som returnerar `none | traffic_no_connection | connection` per dag, gärna med **en** optimerad query per månad.
3. **Tur/retur:** Query-lager + ev. `MRT_find_return_candidates($outbound_connection, $date)` med minsta ankomst→avgång-tid.
4. **Priser:** Ny struktur (t.ex. options eller CPT) kopplad till biljetttyp × åldersgrupp; eventuellt koppling till rutt/tidtabell.
5. **Avvikelser:** Post meta på `mrt_service` eller egen CPT “meddelande” med datumintervall och service-ID.

---

**Relaterade dokument:** [UI_MOCKUP_PLAN.md](UI_MOCKUP_PLAN.md), [mockup-analys-funktionella-krav-user-stories.md](mockup-analys-funktionella-krav-user-stories.md).

*Skapad som underlag för jämförelse mellan krav och implementation (2026).*
