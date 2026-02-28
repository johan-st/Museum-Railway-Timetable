# Framtida arbete – Rekommendationer

Översikt över vad som underlättar framtida utveckling av Museum Railway Timetable.

---

## 1. Prioritet: Hög

### 1.1 Automatiserade tester
**Nuvarande:** Inga tester.

**Rekommendation:** Lägg till PHPUnit för kritiska funktioner.
- **Vad:** Enhetstester för helpers (MRT_validate_time_hhmm, MRT_validate_date, MRT_calculate_direction_from_end_station)
- **Varför:** Säkerställer att refaktorering inte bryter logik
- **Effort:** Låg–medium. WordPress testmiljö kräver wp-env eller liknande

```bash
# Framtida setup (exempel)
composer require --dev phpunit/phpunit
# wp-env start (WordPress test environment)
```

### 1.2 CI (GitHub Actions)
**Nuvarande:** Pre-commit hooks (bash – fungerar inte direkt på Windows utan WSL).

**Rekommendation:** GitHub Actions som kör vid push/PR:
- `composer install && composer lint` (phpstan + phpcs)
- `php scripts/validate.php`
- (Framtida) `composer test`

**Varför:** Fångar fel innan merge, oberoende av lokal miljö.

### 1.3 Uppdatera REFACTORING_PLAN
**Nuvarande:** REFACTORING_PLAN.md innehåller många genomförda punkter som inte är markerade.

**Rekommendation:** Gå igenom och markera klara punkter, ta bort eller arkivera avslutade sektioner. Behåll endast kvarvarande eller framtida idéer.

---

## 2. Prioritet: Medium

### 2.1 En tydlig DEVELOPER.md som ingång
**Nuvarande:** DEVELOPER.md fokuserar på lint. README har mycket.

**Rekommendation:** Gör docs/DEVELOPER.md till **enda ingången** för utvecklare:
- Snabbstart (clone → composer install → deploy)
- Länkar till docs/README.md (alla docs)
- Lint, validate, pre-commit
- Datamodell (länk till DATA_MODEL.md)
- Konstanter (MRT_POST_TYPE_* etc.)

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
| **Validering** | scripts/validate.php, validate.ps1 |
| **Lint** | PHPStan, PHPCS, scripts/lint.ps1 |
| **Deploy** | scripts/deploy.ps1 för Local |
| **Dokumentation** | docs/ med README som index |

---

## 5. Snabbchecklista för nya utvecklare

1. Klona repo
2. `composer install`
3. Kopiera `scripts/deploy.config.example.json` → `scripts/deploy.config.json`, sätt Local-sökväg
4. `.\scripts\deploy.ps1 -OpenBrowser`
5. Läs docs/DEVELOPER.md och docs/STYLE_GUIDE.md
6. Vid ändringar: `composer lint`, `php scripts/validate.php`

---

## 6. Sammanfattning

**Gör först:** CI (GitHub Actions), uppdatera REFACTORING_PLAN, stärk DEVELOPER.md.

**Gör sedan:** Enhetstester för kritiska helpers, integrera lint i validate.

**Överväg:** CHANGELOG.md, pre-commit för Windows.
