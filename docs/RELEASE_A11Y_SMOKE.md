# Release – manuell tillgänglighetsrökning (A11y)

Kort checklista **före release** eller efter större tema-/CSS-ändringar. Automatiserat: `composer plugin-check` och `composer test`. Detta ersätter inte sidvis kontrastgranskning mot produktionstema.

**Relaterat:** [WCAG_JOURNEY_WIZARD.md](WCAG_JOURNEY_WIZARD.md), [WCAG_PUBLIC_SHORTCODES.md](WCAG_PUBLIC_SHORTCODES.md), [VALIDATION.md](VALIDATION.md).

---

## Snabbpass (ca 15–30 min)

- [ ] **`[museum_journey_wizard]`** – Tabba igenom steg; kalender och felmeddelanden; zoom 200 % (se wizard-dokumentet).
- [ ] **`[museum_journey_planner]`** – Sök resa; efter AJAX ska fokus hamna på resultatrubrik eller region; tabell caption hörs i skärmläsare.
- [ ] **`[museum_timetable_month]`** – Månadsnav; klick/tangent på trafikdag; panel under kalendern uppdateras; `aria-pressed` på vald dag.
- [ ] **`[museum_timetable_overview]`** – Landmärke för översikt; varje rutt har rubrik (`h3`); tabba igenom rutnät (tidsceller har beskrivande namn i skärmläsare).
- [ ] **Admin** – Tabba över minst en sida med `mrt-btn`: synlig `:focus-visible`-ring (blå kontur).

---

## Miljö

- Testa med **samma tema och plugins** som produktion om möjligt.
- Skärmläsare: NVDA (Windows), VoiceOver (macOS), eller motsvarande – minst en kort session per release.

---

## Översättningar

Efter nya strängar i kod: uppdatera `languages/*.po`, kompilera till `.mo` enligt [COMPILE_TRANSLATIONS.md](COMPILE_TRANSLATIONS.md).
