# Refactoring Plan – Museum Railway Timetable

Översikt över genomförd uppdelning och eventuella framtida förbättringar.

**Max metodlängd:** 50 rader (STYLE_GUIDE.md + .cursor/rules)

---

## Genomförd uppdelning

### PHP – Mappar och loaders
| Område | Status | Struktur |
|--------|--------|----------|
| admin-meta-boxes | ✅ | inc/admin-meta-boxes/ (station, route, timetable, service, hooks) |
| admin-ajax | ✅ | inc/admin-ajax/ (stoptimes, timetable-services, route-*, journey, timetable-frontend) |
| admin-page | ✅ | inc/admin-page/ (dashboard, clear-db, admin-list) |
| shortcodes | ✅ | inc/shortcodes/ (shortcode-month, shortcode-overview, shortcode-journey) |
| cpt | ✅ | inc/cpt/ (cpt-register, cpt-admin) |
| import-lennakatten | ✅ | inc/import-lennakatten/ (import-data, import-run, loader) |
| functions | ✅ | inc/functions/ (helpers-*, services, timetable-view/) |

### Metodlängd (max 50 rader)
- grid.php, dashboard.php, service.php, assets.php, timetable-services.php, service-stoptimes.php, stoptimes.php – uppdelade
- route.php, timetable.php – redan separata funktioner

### CSS
- admin-base (tokens, components, utilities)
- admin-components (form, ui, width)
- admin-timetable (table, month)
- admin-timetable-overview (layout, cells, components)
- admin-meta-boxes, admin-dashboard, admin-ui, admin-responsive

### JavaScript
- admin.js (init)
- admin-utils.js, admin-route-ui.js, admin-stoptimes-ui.js, admin-timetable-services-ui.js
- admin-service-edit.js (route/destination, stoptimes-formulär)

---

## Eventuella framtida förbättringar

| Område | Idé | Prioritet |
|--------|-----|-----------|
| inc/functions/services.php | Stora fil – överväg services-helpers.php för map_* funktioner | Låg |
| admin.js moduler | Redan uppdelat – inget kvar | – |

---

## Referens: Filstruktur

```
inc/
├── admin-ajax/        # stoptimes, timetable-services, route-destinations, route-stations, journey, timetable-frontend
├── admin-meta-boxes/  # station, route, timetable, timetable-services, timetable-overview, service, service-save, service-stoptimes, hooks
├── admin-page/       # dashboard, clear-db, admin-list
├── shortcodes/       # shortcode-month, shortcode-overview, shortcode-journey
├── cpt/              # cpt-register, cpt-admin
├── import-lennakatten/ # import-data, import-run, loader
└── functions/        # helpers.php (loader), helpers-*.php, services.php, timetable-view/
```
