# Plan: Bryta ner långa filer

Översikt över filstorlekar och föreslagen uppdelning för enklare utveckling.

**Max metodlängd:** 50 rader (STYLE_GUIDE.md + .cursor/rules)

## Metodlängdsrefaktorering (klar)

Alla långa metoder har brutits ner till max 50 rader:
- `grid.php` – MRT_render_timetable_table_body → from/to/regular/transfer rows
- `dashboard.php` – Partials (stats, routes, quick-actions, guide, shortcodes, dev-tools)
- `service.php` – Timetable label, destination field, editing hooks, info/timetable/route/train-type/number/train-types-by-date rows
- `admin-list.php` → inc/admin-page/admin-list.php ✅
- `assets.php` – CSS, JS, localize i egna funktioner
- `timetable-services.php` – Validering, auto title, response
- `service-stoptimes.php` – Instruktioner, tabell
- `stoptimes.php` – Insert per stop extraherat, MRT_validate_stoptime_add_input, MRT_validate_stoptime_update_input

## Ytterligare refaktoreringar (runda 2)

- `services.php` – MRT_get_timetables_for_date, MRT_filter_services_verified_for_date, MRT_map_departure_rows_to_result, MRT_map_connection_rows_to_result (redan separata)
- `timetable-services.php` – MRT_render_timetable_service_row, MRT_render_timetable_new_service_row (redan separata)
- `route.php` – MRT_render_route_related_services, MRT_render_route_info_box, MRT_render_route_end_stations_section, MRT_render_route_stations_table ✅
- `timetable.php` – MRT_render_timetable_date_sections, timetable-dates-script.php (redan extraherat)

## Filuppdelning (runda 3)

- `shortcodes.php` → `inc/shortcodes/` (shortcode-month.php, shortcode-overview.php, shortcode-journey.php)
- `shortcode-journey.php` – MRT_render_journey_form, MRT_render_journey_results_title, MRT_render_journey_connections_table ✅
- `service.php` → service-save.php (save_post callback extraherad)
- `import-lennakatten.php` → `inc/import-lennakatten/` (import-data.php, import-run.php, loader.php) ✅
- `cpt.php` → `inc/cpt/` (cpt-register.php, cpt-admin.php)

## Nuvarande storlekar (uppskattat)

| Fil | Rader | Status |
|-----|-------|--------|
| inc/admin-page.php | ~517 | ✅ Uppdelad (dashboard, clear-db) |
| inc/admin-meta-boxes.php | ~1514 | ✅ Uppdelad |
| assets/admin.css | ~1796 | ✅ Uppdelad (base, timetable, meta-boxes, dashboard, ui, responsive) |
| assets/admin.js | ~1057 | ✅ Uppdelad (utils, route-ui, stoptimes-ui, timetable-services-ui) |
| inc/admin-ajax.php | ~781 | ✅ Uppdelad |
| inc/functions/helpers.php | ~710 | ✅ Uppdelad |
| inc/functions/timetable-view.php | ~420 | ✅ Uppdelad (prepare, grid, overview) |

## 1. admin-meta-boxes.php → inc/admin-meta-boxes/

**Uppdelning:**
- `admin-meta-boxes.php` – Loader som require:ar alla moduler
- `station.php` – Station meta box + save
- `route.php` – Route meta box + save
- `timetable.php` – Timetable meta box + save
- `timetable-services.php` – Timetable services (trips) box
- `timetable-overview.php` – Timetable overview preview
- `service.php` – Service meta box + save
- `service-stoptimes.php` – Stop times box + MRT_render_stoptime_row
- `hooks.php` – Delade hooks (init, block editor, etc.)

## 2. admin-ajax.php → inc/admin-ajax/

**Uppdelning:**
- `admin-ajax.php` – Loader + register
- `stoptimes.php` – add, update, delete, get, save_all
- `timetable-services.php` – add_service, remove_service
- `route-destinations.php` – get_route_destinations
- `route-stations.php` – get_route_stations_for_stoptimes, save_route_end_stations
- `journey.php` – search_journey
- `timetable-frontend.php` – get_timetable_for_date

## 3. helpers.php → inc/functions/

**Uppdelning:**
- `helpers.php` – Loader
- `helpers-stations.php` – get_station_display_name, get_all_stations
- `helpers-routes.php` – route-station, direction, label
- `helpers-services.php` – get_service_train_type, get_service_destination, get_service_stop_times
- `helpers-datetime.php` – validate_date, validate_time, get_current_datetime
- `helpers-connections.php` – find_connecting_services

## 4. admin.js → assets/

**Alternativ A: Flera moduler (enqueue per modul)**
- `admin.js` – Init + utilities
- `admin-route-ui.js` – Route/destination dropdowns
- `admin-stoptimes-ui.js` – Stop times inline editing
- `admin-timetable-services-ui.js` – Timetable services

**Alternativ B: Behåll en fil, men tydliga sektioner**
- Lägg till `// === SECTION: X ===` kommentarer för bättre navigering

## 5. admin-page.php → inc/admin-page/

**Uppdelning (klar):**
- `admin-page.php` – Loader (menu, settings registration)
- `dashboard.php` – MRT_render_admin_page, settings field callbacks
- `clear-db.php` – Clear DB handler (WP_DEBUG only)

## 6. admin.css → assets/

**Uppdelning (klar):**
- `admin-base.css` – Variables, base
- `admin-timetable.css` – Table, month calendar, legend
- `admin-timetable-overview.css` – Grid, cells, overview
- `admin-meta-boxes.css` – Meta box styles
- `admin-dashboard.css` – Dashboard, stats, form elements
- `admin-ui.css` – Status, loading, messages, journey planner
- `admin-responsive.css` – Media queries

**Loader:** inc/assets.php enqueue:ar alla CSS-filer
