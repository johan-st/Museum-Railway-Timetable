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
copy scripts\deploy.config.example.json scripts\deploy.config.json
# Redigera scripts/deploy.config.json med din Local-sökväg

.\scripts\deploy.ps1 -OpenBrowser

# 3. Vid kodändringar – validera innan commit
composer lint
php scripts\validate.php
```

---

## Dokumentation

| Dokument | Innehåll |
|----------|----------|
| [docs/README.md](README.md) | Index över all dokumentation |
| [STYLE_GUIDE.md](STYLE_GUIDE.md) | Kodstandarder, PHP/CSS/JS |
| [COMPONENT_LIBRARY.md](COMPONENT_LIBRARY.md) | UI-komponenter (.mrt-btn, .mrt-form, etc.) |
| [DATA_MODEL.md](DATA_MODEL.md) | Datamodell, relationer, post types |
| [REFACTORING_PLAN.md](REFACTORING_PLAN.md) | Filstruktur, uppdelning |
| [FUTURE_WORK.md](FUTURE_WORK.md) | Rekommendationer för framtida arbete |

---

## Krav

- **PHP** 8.0+
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

```powershell
php scripts\validate.php
```

Kontrollerar: filer, PHP-syntax, ABSPATH, inline styles, plugin header, text domain, CSS/JS.

### Pre-commit hooks

```bash
pip install pre-commit
pre-commit install
```

Kör manuellt: `pre-commit run --all-files`

**OBS:** Kräver bash (WSL eller Git Bash på Windows).

---

## Deploy till Local

1. Kopiera `scripts/deploy.config.example.json` → `scripts/deploy.config.json`
2. Sätt `localPath` och `localUrl` till din Local-site
3. Kör: `.\scripts\deploy.ps1` eller `.\scripts\deploy.ps1 -OpenBrowser`

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
