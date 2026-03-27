# Analys av mockups – funktionella krav och user stories

## Syfte

Detta dokument sammanfattar två delar utifrån mockuperna för Lennakatten:

1. **Funktionella krav**
2. **User stories**

Målet är att beskriva vilken funktionalitet gränssnittet verkar behöva för att stödja sökning, val av datum, val av utresa och återresa samt visning av pris- och trafikdetaljer.

**Jämför med:** [UI_MOCKUP_PLAN.md](UI_MOCKUP_PLAN.md) (teknisk UI-plan: komponenter, design tokens, faser).

---

## 1. Funktionella krav

### 1.1 Resesökning

Systemet ska ge användaren möjlighet att söka en resa genom att ange:

- om resan är **enkel** eller **tur och retur**
- en **startplats**
- en **destination**

#### Krav
- Systemet ska erbjuda val mellan biljettyperna **Enkel** och **Tur och retur**.
- Systemet ska ha ett fält för **Från**.
- Systemet ska ha ett fält för **Till**.
- Systemet ska kunna validera att båda fälten är ifyllda innan sökning genomförs.
- Systemet ska kunna validera att startplats och destination inte är samma plats.
- Systemet ska kunna starta ett sökflöde när användaren klickar på **Sök resa**.

#### Trolig tilläggsfunktionalitet
- Autocomplete för stationer och hållplatser
- Felmeddelanden vid ogiltig inmatning
- Funktion för att byta plats på Från och Till

---

### 1.2 Datumval

Systemet ska låta användaren välja datum för den valda resan.

#### Krav
- Systemet ska visa en kalender med tillgängliga datum.
- Systemet ska kunna markera datum då trafik finns för den valda resan.
- Systemet ska kunna markera datum då trafik finns generellt men inte för den specifika valda sträckan.
- Systemet ska låta användaren navigera mellan månader.
- Systemet ska visa vilken sträcka användaren för närvarande valt.
- Systemet ska inte låta användaren välja datum där resan inte kan genomföras.

#### Affärslogik
- Systemet ska känna till vilka trafikdagar som gäller per datum.
- Systemet ska kunna avgöra om den valda relationen trafikeras ett visst datum.
- Systemet ska kunna ta hänsyn till säsongsdagar, helger eller specialdagar.

---

### 1.3 Val av utresa

Systemet ska visa tillgängliga avgångar för utresan baserat på vald sträcka och valt datum.

#### Krav
- Systemet ska visa en lista med tillgängliga utresor.
- Systemet ska visa avgångstid och ankomsttid för varje resa.
- Systemet ska visa total restid för varje resa.
- Systemet ska tydligt visa om resan är direkt eller innehåller byte.
- Systemet ska visa vilket trafikslag som används i varje resa, exempelvis ångtåg, dieseltåg eller rälsbuss.
- Systemet ska låta användaren välja en utresa.
- Systemet ska kunna expandera en resa för att visa detaljer.

#### Detaljvisning ska kunna innehålla
- delsträckor
- avgångs- och ankomstpunkter
- riktning, exempelvis “mot Faringe”
- mellanliggande stationer
- bytespunkt
- bytestid
- behovsuppehåll

---

### 1.4 Val av återresa

Om användaren valt tur och retur ska systemet visa återresealternativ.

#### Krav
- Systemet ska visa en sammanfattning av vald utresa.
- Systemet ska visa tillgängliga återresor för samma datum eller enligt vald resemodell.
- Systemet ska filtrera återresor så att de är rimliga i förhållande till utresan.
- Systemet ska låta användaren välja en återresa.
- Systemet ska kunna expandera återresor för att visa detaljer på samma sätt som för utresa.

#### Affärslogik
- Systemet ska kunna koppla återresan till vald utresa.
- Systemet ska kunna hantera minsta bytestid eller minsta vistelsetid mellan utresa och återresa.
- Systemet ska kunna avgöra om både ut- och återresa är möjliga samma dag.

---

### 1.5 Prisvisning

Systemet ska kunna visa priser för varje resealternativ.

#### Krav
- Systemet ska visa priser per biljettkategori.
- Systemet ska visa priser för minst följande kategorier:
  - Vuxen
  - Barn 4–15 år
  - Barn 0–3 år
  - Student/senior 65+
- Systemet ska visa pris för minst följande biljetttyper:
  - Enkelbiljett
  - Tur- och returbiljett
  - Heldagsbiljett
- Systemet ska kunna visa prisinformationen i anslutning till den valda eller expanderade resan.

#### Affärslogik
- Systemet ska kunna koppla rätt pris till rätt kategori och biljettyp.
- Systemet ska kunna hantera att olika biljettyper kan visas innan köp genomförs.

---

### 1.6 Trafikmeddelanden och avvikelser

Systemet ska kunna kommunicera avvikelser och särskilda trafikförhållanden.

#### Krav
- Systemet ska kunna visa trafikmeddelanden kopplade till en specifik avgång.
- Systemet ska kunna visa särskilda notiser, till exempel:
  - behovsuppehåll
  - ersatt lok eller fordon
  - driftrelaterad information
- Systemet ska presentera avvikelseinformation tydligt utan att dölja ordinarie reseinformation.

---

### 1.7 Expandera och dölja detaljer

Systemet ska ge användaren möjlighet att visa och dölja mer detaljerad reseinformation.

#### Krav
- Systemet ska ha stöd för expanderbara resekort.
- Systemet ska kunna visa eller dölja passerade stationer.
- Systemet ska kunna visa hela resans struktur vid behov.
- Systemet ska kunna fälla ihop detaljer igen utan att användaren tappar sitt val.

---

### 1.8 Tillstånd och navigering i flödet

Systemet ska hantera användarens resa genom flera steg.

#### Krav
- Systemet ska bevara val av:
  - biljettmodell
  - från och till
  - datum
  - utresa
- Systemet ska låta användaren gå tillbaka i flödet utan att tidigare val försvinner.
- Systemet ska kunna visa aktuell kontext högst upp i vyerna, till exempel vald sträcka och datum.

---

### 1.9 Datamässiga behov bakom gränssnittet

För att stödja mockupernas funktionalitet behöver systemet sannolikt hantera följande datatyper:

- stationer och hållplatser
- relationer mellan stationer
- avgångar per datum
- trafikslag per avgång eller delsträcka
- delsträckor och bytespunkter
- mellanliggande stationer
- prislistor
- trafikmeddelanden
- regler för behovsuppehåll
- kalenderlogik för trafikdagar

---

## 2. User stories

Nedan finns user stories formulerade utifrån vad en resenär sannolikt behöver kunna göra.

### 2.1 Söka resa

**Som resenär vill jag kunna ange varifrån jag reser och vart jag vill åka så att jag kan se vilka resor som finns tillgängliga.**

#### Acceptanskriterier
- Jag kan välja mellan enkelresa och tur och retur.
- Jag kan fylla i Från och Till.
- Jag får inte gå vidare om något fält saknas.
- Jag får ett tydligt felmeddelande om valen är ogiltiga.

---

### 2.2 Välja datum

**Som resenär vill jag kunna se vilka datum min valda resa går så att jag bara väljer datum där det faktiskt finns trafik.**

#### Acceptanskriterier
- Jag ser en kalender med tillgängliga datum.
- Datum där vald resa går är tydligt markerade.
- Datum som inte fungerar för vald resa går inte att välja.
- Jag kan byta månad i kalendern.

---

### 2.3 Se tillgängliga utresor

**Som resenär vill jag kunna se alla utresor för ett valt datum så att jag kan jämföra tider och välja den avgång som passar mig bäst.**

#### Acceptanskriterier
- Jag ser avgångstid, ankomsttid och restid.
- Jag ser om resan är direkt eller innehåller byte.
- Jag kan välja en avgång.
- Jag kan se flera alternativ i en lista.

---

### 2.4 Se detaljer för en resa

**Som resenär vill jag kunna expandera en resa och se detaljer så att jag förstår hur resan går, inklusive byten och passerade stationer.**

#### Acceptanskriterier
- Jag kan öppna en detaljvy för en resa.
- Jag kan se delsträckor och bytespunkter.
- Jag kan se mellanliggande stationer.
- Jag kan dölja detaljer igen.

---

### 2.5 Förstå vilket trafikslag som används

**Som resenär vill jag kunna se om jag åker med ångtåg, dieseltåg eller rälsbuss så att jag vet vilken typ av resa jag väljer.**

#### Acceptanskriterier
- Varje resa visar trafikslag.
- Kombinationsresor visar trafikslag per delsträcka.
- Informationen är synlig både i listan och i detaljvyn där det behövs.

---

### 2.6 Välja återresa

**Som resenär vill jag kunna välja en återresa efter att jag valt min utresa så att jag kan boka en komplett tur- och returresa.**

#### Acceptanskriterier
- Jag ser återresor först efter att en utresa valts.
- Jag ser en sammanfattning av min utresa.
- Jag kan välja ett återresealternativ.
- Återresor som visas är relevanta för min utresa.

---

### 2.7 Se pris innan köp

**Som resenär vill jag kunna se biljettpriser för olika kategorier och biljettyper så att jag kan förstå kostnaden innan jag går vidare.**

#### Acceptanskriterier
- Jag ser priser för vuxen, barn och student/senior.
- Jag ser priser för enkel, tur och retur och heldag.
- Prislistan visas tydligt i anslutning till resan.

---

### 2.8 Förstå avvikelser och särskilda villkor

**Som resenär vill jag kunna se trafikmeddelanden och särskilda villkor för en avgång så att jag inte missar viktig information.**

#### Acceptanskriterier
- Jag ser om en avgång har ett trafikmeddelande.
- Jag ser särskild information om exempelvis behovsuppehåll.
- Avvikelseinformationen är tydlig och lätt att upptäcka.

---

### 2.9 Gå tillbaka utan att börja om

**Som resenär vill jag kunna gå tillbaka i flödet utan att mina tidigare val försvinner så att jag enkelt kan justera ett steg i taget.**

#### Acceptanskriterier
- Mina tidigare val ligger kvar när jag går tillbaka.
- Jag kan ändra datum utan att behöva fylla i hela sökningen igen.
- Jag kan byta utresa utan att behöva börja om från startsidan.

---

### 2.10 Välja en resa som passar mitt upplägg

**Som resenär vill jag kunna jämföra olika resealternativ utifrån tid, restid, byte och trafikslag så att jag kan välja det alternativ som passar mig bäst.**

#### Acceptanskriterier
- Jag kan se flera alternativ samtidigt.
- Jag kan jämföra restider.
- Jag kan se vilka alternativ som innehåller byte.
- Jag kan se skillnad mellan direktresa och kombinationsresa.


---

## 3. Stildetaljer och UI-observationer

Mockuperna innehåller flera tydliga designval som bör dokumenteras eftersom de påverkar både användarupplevelse och implementation.

### 3.1 Visuell identitet

Gränssnittet har en stark visuell identitet med tydlig koppling till den historiska tågmiljön.

#### Observerade stildrag
- En stor bakgrundsbild används genomgående för att skapa kontext och atmosfär.
- Huvudytan ligger i en mörkgrön panel ovanpå bakgrundsbilden.
- Accentfärger i gul/oliv används för:
  - markerade val
  - knappar
  - länkliknande element
  - statusmarkeringar i kalendern
- Vitt och ljusgrått används för innehållskort, tabeller och textbakgrunder.
- Typografin är enkel, tydlig och funktionell med fokus på läsbarhet.

#### Konsekvenser för implementation
- Systemet bör ha ett definierat designsystem med färgtokens för primär bakgrund, panelbakgrund, accent och neutrala ytor.
- Kontrastnivåer bör säkerställas så att gul text på grön bakgrund och grå element fortfarande är tillgängliga.
- Bakgrundsbilden bör kunna skalas responsivt utan att viktig information hamnar bakom huvudpanelen.

---

### 3.2 Layout och struktur

Layouten är centrerad och bygger på en tydlig hierarki där användaren leds steg för steg.

#### Observerade stildrag
- En centrerad innehållspanel fungerar som huvudbehållare.
- Samma panelstil återkommer i flera steg, vilket skapar igenkänning.
- Rubriker är stora, tydliga och placerade högt upp i varje vy.
- Sektioner delas upp med luft och vertikal rytm.
- Det finns en tydlig “kortlayout” för resealternativ.

#### Konsekvenser för implementation
- UI:t bör byggas med återanvändbara layoutkomponenter för:
  - sidpanel
  - formulärsektion
  - resekort
  - detaljsektion
  - prisruta
- Samma spacing-system bör användas konsekvent mellan vyerna.
- Informationshierarkin bör bibehållas även i mobilversioner.

---

### 3.3 Typografi

Typografin signalerar att innehållet är informationsdrivet snarare än dekorativt.

#### Observerade stildrag
- Stora rubriker används för viktiga steg, till exempel “Sök din resa”, “Välj datum”, “Välj utresa” och “Välj återresa”.
- Brödtext och detaljtext är relativt kompakt men fortfarande läsbar.
- Tider och restider ges hög visuell tyngd.
- Vissa etiketter presenteras som små markerade taggar, till exempel trafikslag.

#### Konsekvenser för implementation
- Det bör finnas en tydlig typografisk skala för:
  - sidrubriker
  - sektionsrubriker
  - korttitlar
  - metadata
  - hjälpinformation
- Tider, stationer och valbara alternativ bör ha tydligt definierad fontvikt.
- Små etiketter måste förbli läsbara även på mindre skärmar.

---

### 3.4 Knappar, val och interaktionselement

Mockuperna antyder ett enkelt men tydligt interaktionsmönster.

#### Observerade stildrag
- Primär handling markeras med tydlig gul knapp.
- Val mellan “Enkel” och “Tur och retur” presenteras som segmenterade val.
- “Välj”-knappar i listor är små men visuellt framträdande.
- Expanderbara sektioner markeras med ikon/pil.
- “Tillbaka” visas som enkel textlänk med pil.

#### Konsekvenser för implementation
- Det bör finnas konsekventa komponenter för:
  - primär knapp
  - sekundär knapp
  - segmenterad kontroll
  - expand/collapse-kontroll
  - textlänk för navigering
- Klickytor måste vara tillräckligt stora, särskilt på mobil.
- Valda tillstånd måste vara tydligt särskiljbara från neutrala tillstånd.

---

### 3.5 Kort för resealternativ

Resekorten är centrala i gränssnittet och bär mycket information samtidigt.

#### Observerade stildrag
- Varje resealternativ presenteras i ett eget kort.
- Kortet visar tider, sträcka, trafikslag och ibland restid samt knapp för val.
- Vid expandering visas fler detaljer direkt i samma kort.
- Kortens innehåll verkar strukturerat i tydliga informationsnivåer.

#### Konsekvenser för implementation
- Resekort bör byggas som återanvändbara komponenter med stöd för:
  - kompakt läge
  - expanderat läge
  - statusmeddelanden
  - prissektion
- Kortet bör kunna visa både enkel direktresa och resa med flera delsträckor utan att layouten bryts.
- Detaljvyn bör vara konsekvent mellan utresa och återresa.

---

### 3.6 Ikoner och semantiska markörer

Ikoner används för att förstärka förståelsen av trafikslag och strukturen i resan.

#### Observerade stildrag
- Olika trafikslag markeras med egna visuella symboler.
- Linjer, punkter och noder används för att visa resans förlopp och byten.
- Varningsikoner används för avvikelser och särskild information.
- Kalendern använder färg som semantisk markör.

#### Konsekvenser för implementation
- Ikonuppsättningen bör standardiseras och återanvändas konsekvent.
- Ikoner får inte bära all betydelse själva; de bör kompletteras med text.
- Reselinjen i detaljvisning bör fungera även för längre eller mer komplexa resor.

---

### 3.7 Färgkodning och tillstånd

Färg används inte bara dekorativt utan också funktionellt.

#### Observerade stildrag
- Grönt används som bärande varumärkesfärg och bakgrund för huvudytor.
- Gult/oliv används för aktiva val, knappar och tillgängliga datum.
- Grått används för neutrala eller ej valda alternativ.
- Vitt används för informationsytor med hög läsbarhet.
- Varningsmarkeringar använder avvikande färg för att dra uppmärksamhet till driftmeddelanden.

#### Konsekvenser för implementation
- Färgkodning bör dokumenteras som UI-regler, inte bara som grafiska val.
- Alla tillstånd bör definieras separat:
  - default
  - hover
  - active
  - selected
  - disabled
  - warning
- Viktig information måste även kunna förstås utan färg, för tillgänglighetens skull.

---

### 3.8 Kalenderns visuella logik

Kalendern är inte bara en datumväljare utan också en informationsbärare.

#### Observerade stildrag
- Vissa datum är markerade som tillgängliga för vald resa.
- Andra datum markeras som generellt trafikerade men inte relevanta för det aktuella valet.
- En enkel legend förklarar färgerna.

#### Konsekvenser för implementation
- Kalenderkomponenten behöver stöd för flera visuella tillstånd per dag.
- Legenden bör vara konsekvent och lätt att förstå.
- Datumstatus bör kunna styras av backend-data snarare än hårdkodning.

---

### 3.9 Responsivitet och praktisk användning

Mockuperna visar breda vyer, men funktionaliteten behöver sannolikt fungera även på mindre skärmar.

#### Rekommenderade stilkrav
- Panelen bör kunna skala ned utan att viktiga element hamnar utanför viewport.
- Resekort bör kunna stapla information vertikalt i mobil.
- Prisrader/tabeller bör få ett alternativt mobilvänligt presentationssätt.
- Expanderade detaljvyer bör förbli läsbara utan horisontell scroll om möjligt.
- Bakgrundsbilden bör inte störa läsbarheten på små skärmar.

---

### 3.10 Sammanfattande designprinciper

Utifrån mockuperna verkar följande designprinciper vara centrala:

- tydlig steg-för-steg-navigering
- stark visuell identitet
- hög kontrast mellan innehåll och bakgrund
- kortbaserad informationspresentation
- tydlig användning av färg för status och val
- enkel och funktionell typografi
- möjlighet att visa mycket information utan att hela vyn känns överlastad

---

## 4. Förslag på nästa steg

Det här underlaget lämpar sig väl som grund för nästa nivå av specifikation. Nästa steg kan vara att ta fram:

- epics och features
- klickbar flödeskarta
- datamodell
- API-behov
- tillståndsmodell för frontend
- prioriterad MVP-lista
- UI-specifikation med komponentbibliotek och design tokens

---

## 5. Sammanfattning

Mockuperna pekar på en lösning som i första hand behöver stödja:

- sökning av resa
- val mellan enkel och tur och retur
- datumstyrd trafik
- val av utresa och återresa
- detaljvisning för resor och delsträckor
- pris per kategori och biljettyp
- visning av trafikmeddelanden och särskilda villkor
- en konsekvent visuell stil med tydliga tillstånd och återanvändbara UI-mönster

Det viktigaste bakom gränssnittet verkar vara en tydlig trafiklogik, en bra modell för delsträckor och en prismodell som kan visas tidigt i användarflödet. Utöver detta visar mockuperna också behov av ett genomarbetat UI-system där färger, layout, typografi, kort och interaktioner är tydligt definierade.
