# Component Library – Museum Railway Timetable

Komponentbibliotek för att minska unik CSS. Alla komponenter bygger på design tokens och kan modifieras med utilities.

---

## Namnkonvention (BEM-liknande)

- **Block:** `.mrt-{namn}` (t.ex. `.mrt-btn`)
- **Modifier:** `.mrt-{namn}--{variant}` (t.ex. `.mrt-btn--primary`)
- **Element:** `.mrt-{namn}__{del}` vid behov (t.ex. `.mrt-card__title`)

---

## Komponenter

### Button (.mrt-btn)
Återanvändbar knapp. Använd med modifier för variant.

| Klass | Användning |
|-------|------------|
| `.mrt-btn` | Bas (WP button-styling som fallback) |
| `.mrt-btn--primary` | Primär (blå, journey search) |
| `.mrt-btn--danger` | Farlig åtgärd (röd) |
| `.mrt-btn--block` | Full bredd |
| `.mrt-btn--action` | Snabbåtgärd (text vänster, block strong/span) |

```html
<button class="button mrt-btn mrt-btn--primary">Sök</button>
<a href="..." class="button mrt-btn mrt-btn--block">Ny station</a>
```

### Form (.mrt-form)
Formulärfält och grupper.

| Klass | Användning |
|-------|------------|
| `.mrt-form-group` | Wrapper för label + input |
| `.mrt-form-label` | Label (block, font-weight 600) |
| `.mrt-input` | Input/select (border, padding, focus) |
| `.mrt-input--sm` | Mindre input (t.ex. time 100px) |
| `.mrt-input--meta` | Meta box-fält (max-width 300px) |
| `.mrt-form-row` | Flex-rad (t.ex. datumrad) |
| `.mrt-form-label--inline` | Inline label (t.ex. tabellkontext) |
| `.mrt-form-label__hint` | Hjälptext i label (grå, mindre) |

```html
<div class="mrt-form-group">
  <label class="mrt-form-label">Station</label>
  <select class="mrt-input mrt-w-full">...</select>
</div>
```

### Badge (.mrt-badge)
Liten etikett/chip för nummer eller status.

| Klass | Användning |
|-------|------------|
| `.mrt-badge` | Bas (bakgrund, padding, rounded) |
| `.mrt-badge--muted` | Dämpad variant |

```html
<span class="mrt-badge">71</span>
<span class="mrt-badge mrt-badge--muted">Express</span>
```

### Heading (.mrt-heading)
Rubrik med konsekvent styling.

| Klass | Användning |
|-------|------------|
| `.mrt-heading` | Bas (margin 0) |
| `.mrt-heading--lg` | Större (font-lg) |
| `.mrt-heading--xl` | Extra stor (font-xl) |

```html
<h3 class="mrt-heading mrt-heading--lg">Stations on Route</h3>
```

### Empty state (.mrt-empty)
Tomt tillstånd eller laddning.

| Klass | Användning |
|-------|------------|
| `.mrt-empty` | Tomt tillstånd (grå, italic) |
| `.mrt-empty--loading` | Laddar (med spinner) |

```html
<div class="mrt-alert mrt-alert-info mrt-empty">Inga stationer</div>
<div class="mrt-empty mrt-empty--loading">Laddar...</div>
```

### Card variants
Utökar `.mrt-card` med modifier.

| Klass | Användning |
|-------|------------|
| `.mrt-card--center` | Centrerad text (stat-card) |
| `.mrt-card--warning` | Varningsbakgrund |
| `.mrt-card--compact` | Mindre padding |

### Alert variants
Utökar `.mrt-alert` (redan: info, warning, error).

| Klass | Användning |
|-------|------------|
| `.mrt-alert--empty` | Tomt tillstånd (mrt-none) |

### Table
| Klass | Användning |
|-------|------------|
| `.mrt-table` | Bas tabell |
| `.mrt-table--compact` | Tätare celler |
| `.mrt-col-{n}` | Kolumnbredd (40, 60, 80, 100, 150, 200) |

---

## Width utilities (ersätter mrt-col-*)

| Klass | Bredd |
|-------|-------|
| `.mrt-w-40` | 40px |
| `.mrt-w-60` | 60px |
| `.mrt-w-80` | 80px |
| `.mrt-w-100` | 100px |
| `.mrt-w-150` | 150px |
| `.mrt-w-200` | 200px |

### Code
| Klass | Användning |
|-------|------------|
| `.mrt-code-inline` | Inline kod (grå bakgrund) |
| `.mrt-code-block` | Kodblock (overflow-x auto) |

### Opacity utilities
| Klass | Användning |
|-------|------------|
| `.mrt-opacity-50` | 50 % opacity (disabled) |
| `.mrt-opacity-70` | 70 % opacity |
| `.mrt-opacity-85` | 85 % opacity |
| `.mrt-text-center` | text-align: center |
| `.mrt-p-xl` | padding xl |
| `.mrt-cursor-wait` | cursor: wait |
| `.mrt-cursor-pointer` | cursor: pointer |
| `.mrt-flex-1` | flex: 1 |
| `.mrt-font-medium` | font-weight: 500 |
| `.mrt-font-bold` | font-weight: 700 |
| `.mrt-font-italic` | font-style: italic |
| `.mrt-text-2xl` | font-size: 2rem |
| `.mrt-text-link` | color wp-blue |
| `.mrt-relative` | position: relative |
| `.mrt-overflow-x-auto` | overflow-x: auto |
| `.mrt-border-none` | border: none |
| `.mrt-bg-info` | background info |
| `.mrt-border-b-2` | border-bottom 2px |
| `.mrt-py-sm` | padding top/bottom sm |
| `.mrt-leading-relaxed` | line-height: 1.8 |
| `.mrt-inline-flex` | display: inline-flex |
| `.mrt-items-center` | align-items: center |
| `.mrt-gap-xs` | gap xs |
| `.mrt-font-mono` | font-family: monospace |
| `.mrt-grid-auto-250` | grid minmax(250px, 1fr) |
| `.mrt-dot` / `.mrt-dot--green` | Legend-prick |
| `.mrt-info-box` | Alert med p/strong/ol-styling |

---

## Migreringsregler

1. **Ny CSS?** Kontrollera om en komponent redan finns.
2. **Unik klass?** Ersätt med komponent + modifier + utilities.
3. **Färg?** Använd tokens eller text-utilities (mrt-text-error etc).
4. **Spacing?** Använd margin-utilities (mrt-mt-1 etc).
