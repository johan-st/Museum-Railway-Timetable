# Framtida arbete – Rekommendationer

Översikt över vad som underlättar framtida utveckling av Museum Railway Timetable.

---

## 1. Prioritet: Hög

### 1.1 Automatiserade tester
**Nuvarande:** PHPUnit (`composer test`), se `tests/` och `phpunit.xml.dist`. Stubbar för WP-funktioner i `tests/wp-stubs.php`.

**Nästa steg (valfritt):** Integrationstester mot databas / wp-env för `MRT_find_connections` och full AJAX-kedja där enhetstest inte räcker.

### 1.2 CI (GitHub Actions)
**Nuvarande:** Workflow kör `composer install`, `php scripts/validate.php`, `composer test`.

**Nästa steg (valfritt):** `composer lint` (phpstan + phpcs) i samma eller separat jobb om varningar rensats.

### 1.3 Uppdatera REFACTORING_PLAN
**Status:** ✅ Genomförd uppdelning dokumenterad i REFACTORING_PLAN.md (2026).

### 1.4 PHPStan + WordPress-stubs
**Nuvarande:** `phpstan-bootstrap.php` laddar inte WordPress core; PHPStan rapporterar många "function not found".

**Rekommendation:** Lägg till `phpstan-wordpress` eller motsvarande stubs så att `composer phpstan` blir meningsfullt. Se [DEVELOPER.md](DEVELOPER.md).

---

## 2. Prioritet: Medium

### 2.1 En tydlig DEVELOPER.md som ingång
**Status:** ✅ Uppdaterad med snabbstart, dokumentindex och verktygsanteckningar (PHPStan/PHPCS).

### 2.2 Pre-commit på Windows
**Nuvarande:** `.pre-commit-config.yaml` använder `bash -c`. På Windows krävs WSL eller Git Bash.

**Rekommendation:** 
- Antingen: Dokumentera att pre-commit kräver bash (WSL/Git Bash)
- Eller: Lägg till en Windows-vänlig hook som anropar `.\scripts\lint.ps1`

### 2.3 Integrera lint i validate.php
**Nuvarande:** validate.php kör filkontroller, syntax, ABSPATH. phpstan/phpcs körs separat.

**Rekommendation:** Om `vendor/` finns, låt validate.php anropa `composer lint` (eller phpstan + phpcs) som ett valfritt steg. Ger en enda kommandorad för "är projektet redo?".

### 2.4 CHANGELOG.md
**Nuvarande:** README har changelog-sektion.

**Rekommendation:** Flytta changelog till `CHANGELOG.md` (eller `docs/CHANGELOG.md`). Följ [Keep a Changelog](https://keepachangelog.com/). README länkar till den.

---

## 3. Prioritet: Låg

### 3.1 E2E-tester (Playwright/Cypress)
**Varför:** Journey planner, timetable-overview, admin-flöden är komplexa.
**Effort:** Hög. Kräver stabil testmiljö.

### 3.2 Dependency updates
**Nuvarande:** composer.json med phpstan, phpcs.

**Rekommendation:** `composer update` med jämna mellanrum. Överväg Dependabot för automatiska PR:er.

### 3.3 API-dokumentation
**Nuvarande:** PHPDoc på funktioner. DATA_MODEL.md beskriver strukturen.

**Rekommendation:** Om fler utvecklare ansluter – generera API-docs (t.ex. phpDocumentor) eller underhåll en manuell lista över publika funktioner i docs/.

---

## 4. Redan bra

| Område | Status |
|-------|--------|
| **Filstruktur** | Mappar (inc/, docs/, scripts/), loaders |
| **Style guides** | STYLE_GUIDE, COMPONENT_LIBRARY, DESIGN_SYSTEM |
| **Granskningar** | PHP_STYLE_REVIEW, JS_STYLE_REVIEW, CSS_STYLE_REVIEW |
| **Datamodell** | DATA_MODEL.md med UML |
| **Validering** | `composer plugin-check` (= `php scripts/validate.php`), validate.ps1 |
| **CI** | `.github/workflows/ci.yml` (validate vid push/PR) |
| **Tillgänglighet (dokumenterat)** | WCAG_JOURNEY_WIZARD, WCAG_PUBLIC_SHORTCODES (inkl. overview-grid), RELEASE_A11Y_SMOKE för manuell rökning |
| **Dependabot** | `.github/dependabot.yml` (Composer månadsvis) |
| **Överblick** | [PROJECT_HEALTH.md](PROJECT_HEALTH.md) |
| **Lint** | PHPStan, PHPCS, scripts/lint.ps1 |
| **Deploy** | local/deploy.ps1 för Local |
| **Dokumentation** | docs/ med README som index |

---

## 5. Snabbchecklista för nya utvecklare

1. Klona repo
2. `composer install`
3. Kopiera `local/deploy.config.example.json` → `local/deploy.config.json`, sätt Local-sökväg
4. `.\local\deploy.ps1 -OpenBrowser`
5. Läs docs/DEVELOPER.md och docs/STYLE_GUIDE.md
6. Vid ändringar: `composer plugin-check` (och vid behov `composer lint`)

---

## 6. Sammanfattning

**Gör först:** ~~CI~~ ✅ `.github/workflows/ci.yml`, ~~DEVELOPER~~ ✅, REFACTORING ✅.

**Gör sedan:** Enhetstester för kritiska helpers, PHPStan WordPress-stubs, integrera valfri lint i validate.

**Överväg:** CHANGELOG.md, pre-commit för Windows.
