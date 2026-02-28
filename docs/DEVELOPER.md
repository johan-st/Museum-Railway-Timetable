# Developer Guide

## Nästa steg: Köra lint

Om Composer inte är installerat:

1. **Installera Composer:** https://getcomposer.org/download/
2. **Windows:** Kör installationsprogrammet, starta om terminalen
3. **Alternativt (om PHP finns):**
   ```powershell
   php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
   php composer-setup.php
   php composer.phar install
   ```

Kör sedan:

```powershell
cd c:\Projects\Museum-Railway-Timetable
composer install
composer phpstan
composer phpcs
```

Åtgärda eventuella fel som rapporteras. Committa när allt är grönt.

---

## Kodkvalitet (Linting)

### Krav
- PHP 8.0+
- [Composer](https://getcomposer.org/)

### Installation

```bash
composer install
```

### Köra lint

```bash
# PHPStan (statisk analys)
composer phpstan

# PHPCS (kodstil)
composer phpcs

# Båda
composer lint
```

Windows (PowerShell):
```powershell
.\scripts\lint.ps1
```

### Pre-commit hooks

```bash
pip install pre-commit
pre-commit install
```

Kör manuellt: `pre-commit run --all-files`

## Konstanter

- **MRT_TEXT_DOMAIN** – Text domain för översättningar
- **MRT_POST_TYPE_STATION**, **MRT_POST_TYPE_ROUTE**, **MRT_POST_TYPE_TIMETABLE**, **MRT_POST_TYPE_SERVICE**
- **MRT_TAXONOMY_TRAIN_TYPE**
- **MRT_POST_TYPES** – Array med alla post types

## Typning

Filer använder `declare(strict_types=1)` och type hints där det är möjligt.
