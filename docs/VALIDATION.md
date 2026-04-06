# Validation Guide – Museum Railway Timetable

Manuell checklista och automatiserade kontroller innan release eller deploy.

**Senast uppdaterad:** 2026-03-27

---

## Automatiserat (rekommenderat)

Kör från **projektroten** (mappen som innehåller `museum-railway-timetable.php`):

```powershell
composer plugin-check
# eller: php scripts\validate.php
```

Skriptet kontrollerar bland annat: obligatoriska filer, PHP-syntax, ABSPATH, inline styles (utom CSS-variabler), plugin-header, text domain, CSS/JS-filer.

Se även [DEVELOPER.md](DEVELOPER.md) för `composer lint` (PHPStan + PHPCS) och kända begränsningar.

Efter större UI-/temaändringar: kör även den manuella checklistan i [RELEASE_A11Y_SMOKE.md](RELEASE_A11Y_SMOKE.md) (shortcodes + wizard + admin-fokus).

---

## Filstruktur (manuell checklista)

- [ ] `museum-railway-timetable.php`, `uninstall.php`
- [ ] `inc/` med loaders och undermappar (admin-ajax, admin-meta-boxes, admin-page, …)
- [ ] `assets/` – CSS/JS enligt `inc/assets.php` och [CSS_STRUCTURE.md](../assets/CSS_STRUCTURE.md)
- [ ] `languages/` – `.pot` och översättningar
- [ ] `docs/` – utvecklardokumentation

---

## Säkerhet

- [ ] ABSPATH i alla PHP-filer (utom `uninstall.php` / `validate.php` / `phpstan-bootstrap.php` enligt projektstandard)
- [ ] Sanitize på indata, escape på utdata
- [ ] Nonces för formulär och AJAX
- [ ] `current_user_can` / `MRT_verify_ajax_permission` där det behövs
- [ ] SQL med `$wpdb->prepare()`

---

## Kodkvalitet

- [ ] Följ [STYLE_GUIDE.md](STYLE_GUIDE.md)
- [ ] PHPDoc på nya publika funktioner
- [ ] Text domain `museum-railway-timetable` konsekvent

---

## Manuell testning

- [ ] Aktivera plugin i ren WordPress-miljö
- [ ] Admin: stationer, rutter, tidtabeller, turer, stoptider
- [ ] Shortcodes på sida (månad, översikt, reseplanerare)
- [ ] Webbläsarkonsol utan kritiska fel

---

## Referenser

- [DEVELOPER.md](DEVELOPER.md) – snabbstart och verktyg
- [STYLE_GUIDE.md](STYLE_GUIDE.md) – kodstandard
