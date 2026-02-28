# Översikt över Shortcodes och Komponenter

## Shortcodes (3 st)

### 1. `[museum_timetable_month]` - Månadsvy
Visar en kalendermånadsvy som visar vilka dagar som har turer.

**Användning:**
```
[museum_timetable_month month="2025-06" train_type="" service="" legend="1" show_counts="1"]
```

**Parametrar:**
- `month` - Månad i YYYY-MM format (standard: aktuell månad)
- `train_type` - Filtrera efter train type slug (valfritt)
- `service` - Filtrera efter exakt service title (valfritt)
- `legend` - Visa förklaring (0 eller 1, standard: 1)
- `show_counts` - Visa antal turer per dag (0 eller 1, standard: 1)
- `start_monday` - Börja veckan på måndag (0 eller 1, standard: 1)

**Exempel:**
```
[museum_timetable_month month="2025-06" train_type="steam" show_counts="1"]
```

**Funktioner:**
- Klickbara dagar som visar tidtabell för vald dag
- Visar antal turer per dag
- Filtrering efter train type eller service

---

### 2. `[museum_timetable_overview]` - Komplett Tidtabell
Visar en komplett tidtabell-översikt grupperad per route och riktning.

**Användning:**
```
[museum_timetable_overview timetable_id="123"]
```

**Parametrar:**
- `timetable_id` - Timetable post ID (rekommenderat)
- `timetable` - Timetable namn (alternativ till timetable_id)

**Vad den visar:**
- Alla turer (services) i tidtabellen
- Grupperade per route och riktning (t.ex. "Från Uppsala Ö Till Marielund")
- Train types med ikoner (🚂 Ångtåg, 🚌 Rälsbuss, 🚃 Dieseltåg)
- Tågnummer (eller service ID som fallback)
- Ankomst/avgångstider i HH.MM format för varje station
- Symboler: P (pickup only), A (dropoff only), X (no time), | (passes without stopping)
- Överföringsinformation som visar anslutande tåg vid destinationsstationer
- Riktningspilar (↓) för första och sista stationen
- Special styling för express services (gul vertikal bar)

**Exempel:**
```
[museum_timetable_overview timetable_id="123"]
[museum_timetable_overview timetable="Sommar 2025"]
```

---

### 3. `[museum_journey_planner]` - Reseplanerare
Visar en reseplanerare där användare kan söka efter anslutningar mellan två stationer.

**Användning:**
```
[museum_journey_planner]
```

**Parametrar:**
- `default_date` - Förvalt datum i YYYY-MM-DD format (valfritt, standard: idag)

**Vad den visar:**
- Dropdown för att välja avgångsstation (From)
- Dropdown för att välja ankomststation (To)
- Datumväljare (standard: dagens datum)
- Sökknapp för att hitta anslutningar
- Resultattabell som visar alla tillgängliga anslutningar med avgångs-/ankomsttider, train types och service-information

**Exempel:**
```
[museum_journey_planner]
[museum_journey_planner default_date="2025-06-15"]
```

**Funktioner:**
- Hittar alla services som:
  1. Kör på valt datum
  2. Stannar vid både avgångs- och ankomststationen
  3. Har avgångsstationen före ankomststationen i route-sekvensen
  4. Tillåter pickup vid avgångsstation och dropoff vid ankomststation
- Resultat sorteras efter avgångstid
- Visar meddelanden om inga turer kör eller inga anslutningar hittades

---

## WordPress Widgets

**Inga widgets är för närvarande registrerade.**

Shortcodes kan dock användas i widgets genom att lägga till dem i text-widgets eller custom HTML-widgets.

---

## Frontend Assets

Alla shortcodes använder:
- **CSS**: `assets/admin.css` (delad mellan admin och frontend)
- **JavaScript**: `assets/frontend.js` (för AJAX-funktionalitet)

Assets laddas automatiskt när shortcodes används på sidan.

---

## Framtida Förbättringar

Möjliga framtida tillägg:
- WordPress Widgets för varje shortcode-typ
- Gutenberg Blocks för varje shortcode-typ
- Mer avancerade filter- och sorteringsalternativ
- Export-funktionalitet för tidtabeller

