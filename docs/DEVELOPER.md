# Developer Guide – Museum Railway Timetable

En ingång för utvecklare. Läs detta först.

---

## Snabbstart

```powershell
# 1. Klona och installera
git clone <repo>
cd Museum-Railway-Timetable
composer install

# 2. Deploy till Local (första gången: kopiera config)
copy local\deploy.config.example.json local\deploy.config.json
# Redigera local/deploy.config.json med din Local-sökväg

.\local\deploy.ps1 -OpenBrowser

# 3. Vid kodändringar – validera innan commit
composer plugin-check
# (samma som: php scripts\validate.php)
# Valfritt: composer lint (phpstan + phpcs – se nedan)
```

---

## Dokumentation

| Dokument | Innehåll |
|----------|----------|
| [docs/README.md](README.md) | Fullständigt index över alla `.md`-filer |
| [STYLE_GUIDE.md](STYLE_GUIDE.md) | Kodstandarder, PHP/CSS/JS |
| [COMPONENT_LIBRARY.md](COMPONENT_LIBRARY.md) | UI-komponenter (.mrt-btn, .mrt-form, etc.) |
| [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md) | Design tokens |
| [DATA_MODEL.md](DATA_MODEL.md) | Datamodell, relationer, post types |
| [REFACTORING_PLAN.md](REFACTORING_PLAN.md) | Filstruktur, genomförd uppdelning |
| [VALIDATION.md](VALIDATION.md) | Checklista före deploy |
| [PHP_INSTALL_WINDOWS.md](PHP_INSTALL_WINDOWS.md) | PHP och Composer på Windows |
| [PHP_STYLE_REVIEW.md](PHP_STYLE_REVIEW.md) / [JS_STYLE_REVIEW.md](JS_STYLE_REVIEW.md) / [CSS_STYLE_REVIEW.md](CSS_STYLE_REVIEW.md) | Granskningar mot style guide |
| [FUTURE_WORK.md](FUTURE_WORK.md) | Idéer för framtida förbättringar |
| [PROJECT_HEALTH.md](PROJECT_HEALTH.md) | CI, Dependabot, vilka kommandon som körs |
| [assets/CSS_STRUCTURE.md](../assets/CSS_STRUCTURE.md) | CSS-moduler och `@import` |

---

## Krav

- **PHP** 8.0+ – Se [PHP_INSTALL_WINDOWS.md](PHP_INSTALL_WINDOWS.md) om PHP saknas
- **Composer** – [getcomposer.org](https://getcomposer.org/download/)
- **WordPress** 6.0+ (för testning)

---

## Kodkvalitet

### Lint (PHPStan + PHPCS)

```bash
composer install
composer phpstan    # Statisk analys
composer phpcs      # Kodstil (WordPress)
composer lint       # Båda
```

**Windows (PowerShell):**
```powershell
.\scripts\lint.ps1
```

### Validering

Kör från **projektroten**:

```powershell
php scripts\validate.php
```

Kontrollerar: obligatoriska filer, PHP-syntax, ABSPATH, inline styles, plugin header, text domain, CSS/JS.

### PHPStan och PHPCS (`composer lint`)

- **PHPStan:** Bootstrap laddar inte WordPress core; utan WordPress-stubs kan analysen rapportera många "function not found" för `add_action`, `__`, m.m. Det är ett känt tillstånd. För full analys: lägg till WordPress-stubs (t.ex. `phpstan-wordpress`) – se [FUTURE_WORK.md](FUTURE_WORK.md).
- **PHPCS:** Använder WordPress Coding Standards. Projektet använder prefixet `MRT_` för funktioner; vissa WPCS-regler kan flagga det – bedöm utifrån [STYLE_GUIDE.md](STYLE_GUIDE.md). `composer phpcbf` fixar formateringsbar kod där det passar.

### Pre-commit hooks

```bash
pip install pre-commit
pre-commit install
```

Kör manuellt: `pre-commit run --all-files`

**OBS:** Kräver bash (WSL eller Git Bash på Windows).

---

## Deploy till Local

1. Kopiera `local/deploy.config.example.json` → `local/deploy.config.json`
2. Sätt `localPath` och `localUrl` till din Local-site
3. Kör: `.\local\deploy.ps1` eller `.\local\deploy.ps1 -OpenBrowser`

---

## Konstanter

| Konstant | Användning |
|----------|------------|
| `MRT_TEXT_DOMAIN` | Text domain för översättningar |
| `MRT_POST_TYPE_STATION` | Station post type |
| `MRT_POST_TYPE_ROUTE` | Route post type |
| `MRT_POST_TYPE_TIMETABLE` | Timetable post type |
| `MRT_POST_TYPE_SERVICE` | Service post type |
| `MRT_TAXONOMY_TRAIN_TYPE` | Train type taxonomy |
| `MRT_POST_TYPES` | Array med alla post types |

Definieras i `inc/constants.php`.

---

## Typning

PHP-filer använder `declare(strict_types=1)` och type hints där det är möjligt.
