# Admin Arbetsflöde - Skapa en Tidtabell

Detta dokument beskriver det rekommenderade arbetsflödet för att skapa en komplett tidtabell i admin-gränssnittet.

## Översikt

För att skapa en fungerande tidtabell behöver du:

1. **Stations** - Var tågen stannar
2. **Routes** - Definiera sträckor med stations i ordning
3. **Train Types** (valfritt) - Kategorisera tåg (t.ex. ånglok, diesellok)
4. **Timetables** - Definiera dagar när tidtabellen gäller
5. **Services** - Vilka tåg/turer som finns (kopplade till Timetables och Routes)
6. **Stop Times** - Vilka stationer varje service stannar vid och när

---

## Steg-för-steg Guide

### Steg 1: Skapa Stations

**Varför först?** Stations behövs innan du kan skapa Stop Times.

**Så här gör du:**

1. Gå till **Railway Timetable → Stations** i admin-menyn
2. Klicka på **"Add New"** (eller "Lägg till ny")
3. Fyll i:
   - **Titel**: Stationens namn (t.ex. "Hultsfred Museum")
   - **Station Type**: Välj typ (Station, Halt, Depot, eller Museum)
   - **Latitude/Longitude**: (valfritt) Koordinater för kartvisning
   - **Display Order**: Ordning för sortering (lägre nummer = högre upp)
4. Klicka **"Publish"** (eller "Publicera")

**Tips:**
- Du kan skapa alla stations på en gång, eller skapa dem när du behöver dem
- **Display Order** används för att sortera stations i listor och dropdowns

---

### Steg 2: Skapa Routes

**Varför?** Routes definierar sträckor med stations i ordning. När du skapar en Service kan du välja en Route, och då visas alla stations på sträckan automatiskt så att du enkelt kan välja vilka stations tåget stannar vid.

**Så här gör du:**

1. Gå till **Railway Timetable → Routes** i admin-menyn
2. Klicka på **"Add New"**
3. Fyll i:
   - **Titel**: Route-namnet (t.ex. "Hultsfred - Västervik", "Main Line")
   - Hjälptext visas direkt under title-fältet med exempel
4. I **"Route Stations"** meta box:
   - Välj en station från dropdown
   - Klicka **"Add"** för att lägga till stationen
   - Upprepa för varje station i ordning (första stationen först, sista sist)
   - **Ordna stationer:** Använd ↑ (upp) och ↓ (ner) knapparna för att ändra ordningen
   - **Ta bort station:** Klicka "Remove" för att ta bort en station från rutten
5. Klicka **"Publish"** (eller "Update")

**Tips:**
- Skapa en Route för varje unik sträcka (t.ex. "Nordgående", "Sydgående", "Huvudlinje")
- Stations ordning i Route är viktig - den används när du konfigurerar Stop Times
- **Använd upp/ner-knapparna (↑ ↓) för att enkelt ändra ordningen** - mycket lättare än att ta bort och lägga till igen
- Du kan ha flera Routes med samma stations men i olika ordning
- Exempel: "Hultsfred → Västervik" och "Västervik → Hultsfred" kan vara två olika Routes

---

### Steg 3: Skapa Train Types (Valfritt men Rekommenderat)

**Varför?** Train Types låter dig kategorisera services (t.ex. "Ånglok", "Diesellok", "Elektrisk") och filtrera i shortcodes.

**Så här gör du:**

1. Gå till **Railway Timetable → Train Types**
2. Klicka på **"Add New Train Type"**
3. Fyll i:
   - **Name**: T.ex. "Ånglok", "Diesellok", "Elektrisk"
   - **Slug**: Skapas automatiskt från namnet (t.ex. "steam", "diesel")
4. Klicka **"Add New Train Type"**

**Tips:**
- Du kan lägga till Train Types när som helst
- Services kan ha flera Train Types
- Train Types används för filtrering i shortcodes

---

### Steg 4: Skapa Timetables

**Varför?** Timetables definierar vilka dagar tidtabellen gäller. En timetable kan gälla flera dagar och innehåller flera turer (services).

**Så här gör du:**

1. Gå till **Railway Timetable → Timetables** i admin-menyn
2. Klicka på **"Add New"**
3. I **"Timetable Details"** meta box:
   - **Dates**: Lägg till datum (YYYY-MM-DD) när denna tidtabell gäller
   - Klicka **"Add Date"** för att lägga till fler datum
   - En timetable kan gälla flera dagar (t.ex. alla lördagar i juni)
4. Klicka **"Publish"** (eller "Update")

**Tips:**
- En timetable kan ha flera datum (t.ex. alla helger i en månad)
- Services (turer) tillhör en timetable - de körs på de dagar som timetable definierar
- Du kan skapa olika timetables för olika säsonger (sommar, vinter, helger, etc.)

---

### Steg 5: Lägg till Turer i Timetable (Rekommenderat)

**Varför?** Det enklaste sättet att hantera turer är direkt i tidtabellen. Varje tur representerar ett tåg med specifika tider.

**Så här gör du:**

1. Gå till **Railway Timetable → Timetables**
2. Öppna eller skapa en timetable
3. I **"Trips (Services)"** meta box:
   - **Route**: Välj en route (obligatoriskt)
   - **Train Type**: Välj tågtyp (valfritt, t.ex. "Ånglok", "Diesellok")
   - **Direction**: Välj riktning (valfritt: "Dit" eller "Från")
   - Klicka **"Add Trip"** - turen skapas automatiskt och kopplas till timetable
4. **Turen får automatiskt ett namn** baserat på Route + Direction (t.ex. "Hultsfred → Västervik - Dit")
5. Klicka **"Edit"** på en tur för att konfigurera Stop Times

**Tips:**
- **Route är obligatoriskt** - Du måste välja en Route innan du kan lägga till en tur
- Du kan lägga till flera turer med olika tågtyper och riktningar på samma timetable
- **Se översikten** i "Timetable Overview" meta box för att se hur tidtabellen ser ut
- Turen tillhör automatiskt timetable - du behöver inte välja timetable separat

### Alternativ: Skapa Services separat

Om du föredrar att skapa services separat:

1. Gå till **Railway Timetable → Services**
2. Klicka på **"Add New"**
3. Fyll i:
   - **Timetable**: **VÄLJ EN TIMETABLE** (obligatoriskt) - Välj den timetable som denna service tillhör
   - **Route**: **VÄLJ EN ROUTE** (obligatoriskt) - Välj den Route som denna service kör på
   - **Train Type**: Välj tågtyp (t.ex. "Ånglok", "Diesellok")
   - **End Station**: Välj slutstation/destination (valfritt, rekommenderat)
   - **Train Number**: Ange tågnummer som visas i tidtabeller (valfritt, t.ex. "71", "91")
4. Klicka **"Publish"** (eller "Update")

**Tips:**
- **Timetable är obligatoriskt** - Service tillhör en timetable (som definierar vilka dagar den körs)
- **Route är obligatoriskt** - Du måste välja en Route innan du kan konfigurera Stop Times
- Skapa en service för varje unik tur
- Du kan skapa många services med olika tider på samma Route och Timetable

---

### Steg 6: Se Timetable Overview

**Vad är Timetable Overview?** En visuell förhandsvisning av hela tidtabellen, grupperad efter route och riktning.

**Så här gör du:**

1. I **Timetable edit screen**, scrolla ner till **"Timetable Overview"** meta box
2. Här ser du:
   - Alla turer grupperade efter route och riktning (t.ex. "Från Uppsala Ö Till Marielund")
   - Tågtyp för varje tur (Ångtåg, Rälsbuss, Dieseltåg)
   - Tider för varje station
   - "X" markerar tider som är null/ej specificerade

**Tips:**
- Översikten uppdateras automatiskt när du lägger till eller tar bort turer
- Använd översikten för att snabbt kontrollera att allt ser rätt ut
- Layouten liknar traditionella tryckta tidtabeller

---

### Steg 7: Konfigurera Stop Times för varje Service

**Vad är Stop Times?** Detta definierar vilka stationer varje service stannar vid, i vilken ordning, och när (ankomst/avgångstider).

**Så här gör du:**

1. Gå till **Railway Timetable → Services**
2. Öppna en service för redigering (klicka på titeln)
3. **VIKTIGT**: Kontrollera att du har valt en **Route** i "Service Details" meta box. Om inte, välj en Route och klicka "Update".
4. Scrolla ner till **"Stop Times"** meta box
5. Du ser nu alla stations på Route:n i en tabell:
   - **Order**: Stationsordning på sträckan (1, 2, 3...)
   - **Station**: Stationsnamn
   - **Stops here**: Checkbox - kryssa i för varje station där tåget stannar
   - **Arrival**: Ankomsttid (HH:MM) - lämna tomt om tåget stannar men tiden inte är fast
   - **Departure**: Avgångstid (HH:MM) - lämna tomt om tåget stannar men tiden inte är fast
   - **Pickup/Dropoff**: Kryssa i om passagerare kan gå på/av
   - **Symboler i tidtabeller**: 
     - **P**: Endast påstigning (pickup only)
     - **A**: Endast avstigning (dropoff only)
     - **X**: Tåget stannar men ingen tid angiven
     - **|**: Tåget passerar utan att stanna (no pickup, no dropoff)
6. För varje station där tåget stannar:
   - Kryssa i **"Stops here"**
   - Fyll i **Arrival** och/eller **Departure** tider
   - Välj **Pickup** och/eller **Dropoff** om tillämpligt
7. Klicka **"Save Stop Times"** längst ner för att spara alla ändringar

**Tips:**
- **Route måste väljas först** - Om ingen Route är vald visas ett meddelande
- När du kryssar i "Stops here" aktiveras tidsfälten automatiskt
- **Tider kan vara tomma** - Om tåget stannar men tiden inte är fast, lämna Arrival/Departure tomt
- Du kan välja att tåget ska köra förbi vissa stations (lämna "Stops here" avkryssat)
- Alla ändringar sparas på en gång när du klickar "Save Stop Times"

**Exempel:**
```
Order | Station          | Stops | Arrival | Departure | Pickup | Dropoff
------|------------------|-------|---------|-----------|--------|--------
1     | Hultsfred Museum | ✓     | —       | 10:00     | ✓      | ✓
2     | Västervik        | ✓     | 10:30   | 10:35     | ✓      | ✓
3     | Oskarshamn       | ✓     | 11:00   | —         | ✓      | ✓
4     | Kalmar           | —     | —       | —         | —      | —
```

I exemplet ovan stannar tåget vid de tre första stations men kör förbi Kalmar.

---

## Komplett Exempel: Skapa en Säsongstidtabell

Låt oss säga att du vill skapa en säsongstidtabell för juni–augusti med:
- Vardagstidtabell (måndag–fredag)
- Helgtidtabell (lördag–söndag)
- Specialtidtabell för 4 juli

### Steg 1: Skapa Stations
1. Skapa "Hultsfred Museum"
2. Skapa "Västervik"
3. Skapa "Oskarshamn"

### Steg 2: Skapa Route
1. Skapa Route "Hultsfred → Västervik"
2. Lägg till stations i ordning: Hultsfred Museum, Västervik, Oskarshamn

### Steg 3: Skapa Train Types
1. Skapa "Ånglok" (slug: "steam")
2. Skapa "Diesellok" (slug: "diesel")

### Steg 4: Skapa Timetables
1. Skapa "Vardagar i juni-augusti" med datum: 2025-06-01, 2025-06-02, ... (alla vardagar)
2. Skapa "Helger i juni-augusti" med datum: 2025-06-07, 2025-06-08, ... (alla helger)
3. Skapa "4 juli" med datum: 2025-07-04

### Steg 5: Skapa Services
1. Skapa "09:00 Avgång" (Timetable: "Vardagar i juni-augusti", Route: "Hultsfred → Västervik", Train Type: Ånglok, Direction: Dit)
2. Skapa "10:00 Avgång" (Timetable: "Helger i juni-augusti", Route: "Hultsfred → Västervik", Train Type: Ånglok, Direction: Dit)
3. Skapa "14:00 Avgång" (Timetable: "4 juli", Route: "Hultsfred → Västervik", Train Type: Ånglok, Direction: Dit)

### Steg 6: Konfigurera Stop Times

**För "09:00 Avgång":**
- Kryssa i "Stops here" för alla tre stations
- Hultsfred Museum: Departure 09:00
- Västervik: Arrival 09:30, Departure 09:35
- Oskarshamn: Arrival 10:00
- Klicka "Save Stop Times"

**För "10:00 Avgång":**
- Kryssa i "Stops here" för alla tre stations
- Hultsfred Museum: Departure 10:00
- Västervik: Arrival 10:30, Departure 10:35
- Oskarshamn: Arrival 11:00
- Klicka "Save Stop Times"

**För "14:00 Avgång":**
- Kryssa i "Stops here" för alla tre stations
- Hultsfred Museum: Departure 14:00
- Västervik: Arrival 14:30, Departure 14:35
- Oskarshamn: Arrival 15:00
- Klicka "Save Stop Times"

---

## Tips och Best Practices

1. **Börja med Stations** - De behövs för allt annat
2. **Skapa Routes först** - Routes definierar sträckor och gör det enklare att konfigurera Services
3. **Använd upp/ner-knapparna (↑ ↓)** - Mycket enklare att ordna stationer i Routes än att ta bort och lägga till igen
4. **Använd Display Order** - Sortera stations logiskt (1, 2, 3...) i Stations
5. **Skapa Timetables** - Definiera vilka dagar tidtabellen gäller
6. **Tydliga Service-namn** - T.ex. "09:00 Avgång" istället för "Service 1"
7. **Välj Timetable och Route** - Båda måste väljas innan du kan konfigurera Stop Times
8. **Använd "Stops here" checkbox** - Enkelt att välja vilka stations tåget stannar vid
9. **Tider kan vara tomma** - Om tåget stannar men tiden inte är fast, lämna Arrival/Departure tomt
10. **Spara Stop Times på en gång** - Klicka "Save Stop Times" när du är klar med alla ändringar
11. **Använd Train Types** - Gör det enklare att filtrera i shortcodes
12. **Spara ofta** - Klicka "Update" efter varje större ändring
13. **Skapa många Services** - Du kan skapa många services (t.ex. 12 avgångar) med olika tider på samma Route och Timetable

---

## Felsökning

**Problem: Service visas inte i shortcode**
- Kontrollera att Timetable innehåller rätt datum
- Kontrollera att Service är kopplad till rätt Timetable
- Kontrollera att Stop Times finns för servicen

**Problem: Fel stationer visas**
- Kontrollera att rätt Route är vald för servicen
- Kontrollera att "Stops here" är ikryssat för rätt stations
- Kontrollera stations ordning i Route:n (använd upp/ner-knapparna för att ändra)

**Problem: Tider visas inte**
- Kontrollera att Arrival/Departure-tider är korrekta (HH:MM-format)
- Tider kan vara tomma om tåget stannar men tiden inte är fast

---

## Nästa Steg

När du har skapat din tidtabell kan du:

1. **Visa den på frontend** - Använd shortcodes (se README.md)
2. **Kontrollera Stations Overview** - Se översikt över alla stations
3. **Testa shortcodes** - Verifiera att allt fungerar korrekt

