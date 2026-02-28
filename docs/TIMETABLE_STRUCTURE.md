# Tidtabellstruktur - Analys av PDF-layout

## Översikt
Detta dokument beskriver strukturen för den gröna tidtabellen (Gron-tidtabell-2025-Y-B.pdf) och hur den är uppdelad.

## Tabellstruktur

### Header-sektion (2 rader)

```
┌─────────────────┬──────────┬──────────┬──────────┬──────────┬──────────┬──────────┐
│                 │ Ångtåg   │ Rälsbuss  │ Ångtåg   │ Dieseltåg │ Dieseltåg │ Rälsbuss  │
│   Station       ├──────────┼──────────┼──────────┼──────────┼──────────┼──────────┤
│                 │ 71       │ 91        │ 73       │ 63       │ 65       │ 75       │
└─────────────────┴──────────┴──────────┴──────────┴──────────┴──────────┴──────────┘
```

**Rad 1 (Tågtyper):**
- Visar tågtyp för varje kolumn (Ångtåg, Rälsbuss, Dieseltåg, etc.)
- Varje kolumn representerar en service/tåg

**Rad 2 (Tågnummer):**
- Visar tågnummer för varje service (71, 91, 73, etc.)
- Kan innehålla speciallabels (t.ex. "Thun's-expressen")

### Body-sektion

#### 1. "Från [station]" rad
```
┌─────────────────┬──────────┬──────────┬──────────┬──────────┬──────────┬──────────┐
│ Från Uppsala Ö │ 10.10    │ 11.10    │ 12.38    │ 14.30    │ 16.15    │ 18.05    │
└─────────────────┴──────────┴──────────┴──────────┴──────────┴──────────┴──────────┘
```
- Visar avgångstider från första stationen
- Markerad med blå bakgrund i vår implementation
- Kursiv text

#### 2. Vanliga stationer
```
┌─────────────────┬──────────┬──────────┬──────────┬──────────┬──────────┬──────────┐
│ Fyrislund       │ P 10.13  │ P 11.13  │ P 12.41  │ P 14.33  │ P 16.18  │ X 18.08  │
│ Årsta           │ P 10.15  │ P 11.15  │ P 12.43  │ P 14.35  │ P 16.20  │ X 18.10  │
│ Skölsta        │ X 10.19  │ X 11.18  │ X 12.47  │ X 14.39  │ X 16.24  │ X 18.13  │
│ Bärby           │ 10.32    │ 11.28    │ 13.00    │ 14.50    │ 16.35    │ 18.23    │
│ Gunsta          │ X 10.33  │ X 11.31  │ X 13.01  │ X 14.51  │ X 16.36  │ X 18.24  │
└─────────────────┴──────────┴──────────┴──────────┴──────────┴──────────┴──────────┘
```

**Symboler:**
- **P** = Tåget stannar endast om det finns påstigande passagerare
- **A** = Tåget stannar endast om det finns avstigande passagerare (buss)
- **X** = Tåget stannar endast om det finns av- eller påstigande passagerare
- **|** = Tåget passerar utan att stanna
- **—** = Tåget kör inte denna sträcka
- **Tid (t.ex. 10.13)** = Normal hållplats, tåget stannar alltid

#### 3. "Till [station]" rad
```
┌─────────────────┬──────────┬──────────┬──────────┬──────────┬──────────┬──────────┐
│ Till Marielund  │ 10.42    │ 11.38    │ 13.10    │ 15.00    │ 16.45    │ 18.31    │
└─────────────────┴──────────┴──────────┴──────────┴──────────┴──────────┴──────────┘
```
- Visar ankomsttider till sista stationen i denna del av rutten
- Markerad med blå bakgrund
- Kursiv text

#### 4. "Tågbyte:" rad
```
┌─────────────────┬──────────┬──────────┬──────────┬──────────┬──────────┬──────────┐
│ Tågbyte:        │ Dieseltåg│          │          │ Rälsbuss │          │          │
│                 │ 84       │          │          │ 93       │          │          │
└─────────────────┴──────────┴──────────┴──────────┴──────────┴──────────┴──────────┘
```
- Visar anslutande tåg vid byte
- Markerad med gul bakgrund
- Kursiv text
- Visar tågnummer och eventuellt avgångstid för anslutande tåg

#### 5. Fortsättning med ny "Från [station]" rad
```
┌─────────────────┬──────────┬──────────┬──────────┬──────────┬──────────┬──────────┐
│ Från Marielund  │ 10.55    │ 11.43    │ 13.32    │ 15.25    │ 17.05    │ 18.41    │
└─────────────────┴──────────┴──────────┴──────────┴──────────┴──────────┴──────────┘
```
- När rutten fortsätter efter ett byte, visas en ny "Från"-rad
- Samma struktur upprepas för nästa del av rutten

## Komplett exempel - Första delen av rutten

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│                         GRÖN TIDTABELL – bussanslutningar till Fjällnora            │
└─────────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────┬──────────┬──────────┬──────────┬──────────┬──────────┬──────────┐
│                 │ Ångtåg   │ Rälsbuss │ Ångtåg   │ Dieseltåg│ Dieseltåg│ Rälsbuss │
│   Station       ├──────────┼──────────┼──────────┼──────────┼──────────┼──────────┤
│                 │ 71       │ 91       │ 73       │ 63       │ 65       │ 75       │
├─────────────────┼──────────┼──────────┼──────────┼──────────┼──────────┼──────────┤
│ Från Uppsala Ö │ 10.10    │ 11.10    │ 12.38    │ 14.30    │ 16.15    │ 18.05    │
├─────────────────┼──────────┼──────────┼──────────┼──────────┼──────────┼──────────┤
│ Fyrislund       │ P 10.13  │ P 11.13  │ P 12.41  │ P 14.33  │ P 16.18  │ X 18.08  │
│ Årsta           │ P 10.15  │ P 11.15  │ P 12.43  │ P 14.35  │ P 16.20  │ X 18.10  │
│ Skölsta        │ X 10.19  │ X 11.18  │ X 12.47  │ X 14.39  │ X 16.24  │ X 18.13  │
│ Bärby           │ 10.32    │ 11.28    │ 13.00    │ 14.50    │ 16.35    │ 18.23    │
│ Gunsta          │ X 10.33  │ X 11.31  │ X 13.01  │ X 14.51  │ X 16.36  │ X 18.24  │
├─────────────────┼──────────┼──────────┼──────────┼──────────┼──────────┼──────────┤
│ Till Marielund  │ 10.42    │ 11.38    │ 13.10    │ 15.00    │ 16.45    │ 18.31    │
├─────────────────┼──────────┼──────────┼──────────┼──────────┼──────────┼──────────┤
│ Tågbyte:        │ Dieseltåg│          │          │ Rälsbuss │          │          │
│                 │ 84       │          │          │ 93       │          │          │
└─────────────────┴──────────┴──────────┴──────────┴──────────┴──────────┴──────────┘
```

## Färgkodning (i vår implementation)

### Service-kolumner
- **Blå bakgrund** = Buss (Rälsbuss)
- **Gul bakgrund med gul vänsterkant** = Specialtåg (Express, etc.)
- **Vit/standard bakgrund** = Vanliga tåg (Ångtåg, Dieseltåg)

### Rader
- **Blå bakgrund** = "Från" och "Till" rader
- **Gul bakgrund** = "Tågbyte:" rad
- **Ljusblå bakgrund** = Highlightade rader (överföringsstationer)
- **Vit/alternerande** = Vanliga stationer

## Dataflöde

### 1. Header-generering
```
För varje service i services_list:
  - Hämta train_type → Rad 1 (Tågtyper)
  - Hämta service_number → Rad 2 (Tågnummer)
  - Bestäm CSS-klasser (bus, special, etc.)
```

### 2. Body-generering
```
1. "Från [första_station]" rad
   - Hämta stop_times för första stationen
   - Visa departure_time (eller arrival_time om departure saknas)

2. För varje station i route:
   - Hämta stop_times för stationen
   - Bestäm symbol (P, A, X, |, eller ingen)
   - Formatera tid (HH.MM)
   - Rendera cell

3. "Till [sista_station]" rad
   - Hämta stop_times för sista stationen
   - Visa arrival_time (eller departure_time om arrival saknas)

4. "Tågbyte:" rad (om connections finns)
   - Hämta connecting_services för varje service
   - Visa service_number och departure_time för anslutande tåg
```

## Symbol-logik

```
IF stop_time EXISTS:
  IF pickup_allowed = false AND dropoff_allowed = false:
    → Visa "|" (tåget passerar)
  ELSE IF pickup_allowed = true AND dropoff_allowed = false:
    → Visa "P [tid]" (endast påstigning)
  ELSE IF pickup_allowed = false AND dropoff_allowed = true:
    → Visa "A [tid]" (endast avstigning)
  ELSE IF pickup_allowed = true AND dropoff_allowed = true:
    IF departure_time EXISTS:
      → Visa "[tid]" (normal hållplats)
    ELSE IF arrival_time EXISTS:
      → Visa "[tid]" (normal hållplats)
    ELSE:
      → Visa "X" (stannar om passagerare, men ingen tid angiven)
ELSE:
  → Visa "—" (tåget kör inte denna sträcka)
```

## Implementation i kod

### Fil: `inc/functions/timetable-view.php`

**Funktion:** `MRT_render_timetable_overview()`

**Struktur:**
1. Gruppera services efter route och direction
2. För varje grupp:
   - Skapa tabell med 2 header-rader
   - Lägg till "Från"-rad
   - Lägg till vanliga stationer
   - Lägg till "Till"-rad
   - Lägg till "Tågbyte"-rad (om connections finns)

### CSS-klasser

**Header:**
- `.mrt-station-col` - Station-kolumnen (rowspan=2)
- `.mrt-service-col` - Service-kolumn
- `.mrt-train-type` - Tågtyp-text
- `.mrt-service-number` - Tågnummer

**Body:**
- `.mrt-from-row` - "Från"-rad
- `.mrt-to-row` - "Till"-rad
- `.mrt-transfer-row` - "Tågbyte"-rad
- `.mrt-time-cell` - Tids-cell
- `.mrt-service-bus` - Buss-kolumn
- `.mrt-service-special` - Specialtåg-kolumn

## Responsiv design

### Desktop (> 768px)
- Full tabell med alla kolumner synliga
- Sticky första kolumn (Station)
- Horisontell scroll om nödvändigt

### Tablet (782px - 768px)
- Kompaktare padding
- Mindre fontstorlekar
- Fortfarande tabell-layout

### Mobile (< 768px)
- Konvertera till card-layout
- Varje station blir en card
- Services visas som labels i cards
- "Från"/"Till"/"Tågbyte" rader behåller sin betydelse

## Förklaringar (från PDF)

| Symbol | Betydelse |
|--------|-----------|
| **X** | Tåget stannar endast om det finns av- eller påstigande passagerare. Säg till konduktören om du vill stiga av eller ge en tydlig signal till föraren om du vill stiga på. Observera att tiderna här är ungefärliga. |
| **P** | Tåget stannar endast om det finns påstigande passagerare. Ge en tydlig signal till föraren om du vill stiga på. Observera att tiderna här är ungefärliga. |
| **A** | Bussen stannar vid närliggande busshållplats om det finns avstigande passagerare. Säg till föraren när du stiger på bussen var du vill stiga av. |
| **(tomt)** | Bussen gör inget uppehåll vid stationen/hållplatsen. |
| **Blåa fält** | Blåa fält är anslutningsbussar till/från Fjällnora. |
| **Thun's-expressen** | Thun's-expressen tar dig till och från klädvaruhuset Thun's i Faringe. |

## Noteringar

- Tider formateras som HH.MM (punkt istället för kolon)
- "Från" och "Till" rader används för att tydligt markera start och slut på en rutt-del
- "Tågbyte:" rad visas endast om det finns anslutande tåg
- Varje route-grupp kan ha flera delar (t.ex. Uppsala Ö → Marielund, sedan Marielund → Faringe)
- Buss-kolumner har blå bakgrund för visuell åtskillnad
- Specialtåg har gul bakgrund med gul vänsterkant
