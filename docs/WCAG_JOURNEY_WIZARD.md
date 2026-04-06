# WCAG – [museum_journey_wizard] (publik reseplanerare)

Mål: **WCAG 2.1 nivå AA** där det är tekniskt rimligt utan att duplicera temats ansvar. Detta dokument beskriver **vad som är implementerat** och **vad som bör testas manuellt** vid varje större ändring.

**Relaterat:** [UI_MOCKUP_PLAN.md](UI_MOCKUP_PLAN.md), [ARCHITECTURE.md](ARCHITECTURE.md).

---

## 1. Implementerade mönster

| Område | Åtgärd |
|--------|--------|
| **Landmärken** | Varje steg är `role="region"` med `aria-labelledby` mot rubrik (inte `tabpanel` utan fliklista – undviker fel ARIA-mönster). |
| **Steglista** | `<nav>` med `aria-label`; aktivt steg `aria-current="step"`. |
| **Felmeddelanden** | `role="alert"`, `aria-live="assertive"`, `aria-relevant="additions text"`, unikt `id` på behållaren. |
| **Kalender** | Omgivande `role="region"` + `aria-label`; månadsnavigation med `aria-label`; `aria-busy` under laddning; dagknappar med beskrivande `aria-label` (datum + status); valbar dag `aria-pressed`. |
| **Tabeller** | `caption` på utresa/retur-tabeller; `scope="col"` på rubriker; dold rubrik ”Actions” / ”Ticket type” med `.mrt-sr-only` där kolumnen är visuellt tom. |
| **Knappar** | Välj resa / visa stopp: unika `aria-label` med tjänst och tider. |
| **Fokus** | Vid stegbyte flyttas fokus till rubrik (`tabindex="-1"`), tas bort vid `blur` (inte kvar i tabbordning). |
| **Legend** | Färgprover `aria-hidden="true"`; listan har `aria-label` för gruppen. |
| **Radiogrupp** | Insynliga radioknappar med clip/sr-only-liknande CSS så tangentbordsfokus behålls. |
| **Rörelse** | `prefers-reduced-motion: reduce` minskar animation/transition i wizard-containern. |
| **Tangentbord** | `:focus-visible` på kalenderdagar för synlig fokusmarkering. |

---

## 2. Manuell checklista (vid release eller temaändring)

- [ ] Tabba igenom hela flödet: rutt → datum → utresa → ev. retur → sammanfattning; ingen fångad fokus.
- [ ] Skärmläsare (NVDA/JAWS/VoiceOver): stegnamn, feltext, kalender månad, dagstatus.
- [ ] Zoom 200 %: inga överlappade kritiska kontroller.
- [ ] Kontrast: hero-panel (grön bakgrund / vit text / gul knapp) mot aktuellt tema – vid avvikelse justera tokens i `journey-wizard.css` eller tema.
- [ ] Färger är inte enda bärare av information (legend + text).

---

## 3. Kända gränser

- **Full WCAG-certifiering** kräver sidvis granskning (t.ex. kontrast mot varje temats brödtext).
- **Tredjepartstema** kan åsidosätta plugin-CSS; testa på målproduktion.
- **Integrationstester** med skärmläsare automatiseras sällan; manuell rökning rekommenderas.

---

## 4. Referenser

- [WCAG 2.1](https://www.w3.org/TR/WCAG21/)
- [WAI-ARIA Practices – multi-step forms](https://www.w3.org/WAI/ARIA/apg/) (mönster anpassade till regioner + rubrikfokus)
